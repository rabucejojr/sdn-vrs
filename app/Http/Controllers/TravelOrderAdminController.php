<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTravelOrderRequest;
use App\Models\TravelOrder;
use App\Models\TravelOrderLog;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\TravelOrderIssuedNotification;
use App\Support\TravelOrderPersonnel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TravelOrderAdminController extends Controller
{
    public function issue(TravelOrder $travelOrder): RedirectResponse
    {
        abort_if($travelOrder->status !== 'draft', 403);

        $travelOrder->update([
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        // Notify passengers who are linked to system users
        $travelOrder->load('passengers');
        $userIds = $travelOrder->passengers->pluck('user_id')->filter()->unique();

        if ($userIds->isNotEmpty()) {
            $recipients = User::whereIn('id', $userIds)->get();
            foreach ($recipients as $user) {
                $user->notify(new TravelOrderIssuedNotification($travelOrder));
            }
        }

        return redirect()->route('travel-orders.show', $travelOrder->travel_order_number)
            ->with('success', 'Travel Order issued.');
    }

    public function cancel(TravelOrder $travelOrder): RedirectResponse
    {
        abort_if($travelOrder->status === 'cancelled', 403);

        $travelOrder->update(['status' => 'cancelled']);

        return redirect()->route('travel-orders.show', $travelOrder->travel_order_number)
            ->with('success', 'Travel Order cancelled.');
    }

    public function generateForm(TripTicket $ticket): Response|RedirectResponse
    {
        abort_if($ticket->status !== 'approved', 403, 'Travel Orders can only be generated from approved reservations.');

        // If a TO already exists for this ticket, redirect to it
        if ($ticket->travelOrder) {
            return redirect()->route('travel-orders.show', $ticket->travelOrder->travel_order_number)
                ->with('info', "A Travel Order ({$ticket->travelOrder->travel_order_number}) already exists for this reservation.");
        }

        $ticket->load('passengers');

        // Pre-build passenger list: driver first (if set), then trip ticket passengers
        $passengers = collect();

        if ($ticket->driver_name) {
            $passengers->push(['name' => strtoupper($ticket->driver_name), 'designation' => 'Driver', 'user_id' => null]);
        }

        foreach ($ticket->passengers as $p) {
            $passengers->push(['name' => $p->name, 'designation' => $p->designation ?? '', 'user_id' => null]);
        }

        return Inertia::render('Admin/TravelOrders/GenerateFromTicket', [
            'ticket' => $ticket->only('ticket_number', 'purpose', 'destination'),
            'prefill' => [
                'purpose' => $ticket->purpose,
                'date_start' => $ticket->date_start->format('Y-m-d'),
                'date_end' => $ticket->date_end->format('Y-m-d'),
                'passengers' => $passengers->values()->all(),
            ],
            'users' => User::orderBy('name', 'asc')->get(['id', 'name']),
            'vehicles' => Vehicle::where('is_active', '=', true)->get(['id', 'name', 'plate_number']),
            'transportationModes' => TravelOrder::TRANSPORTATION_MODES,
        ]);
    }

    public function generate(StoreTravelOrderRequest $request, TripTicket $ticket): RedirectResponse
    {
        abort_if($ticket->status !== 'approved', 403, 'Travel Orders can only be generated from approved reservations.');

        if ($ticket->travelOrder) {
            return redirect()->route('travel-orders.show', $ticket->travelOrder->travel_order_number)
                ->with('info', 'A Travel Order already exists for this reservation.');
        }

        $validated = $request->validated();

        $to = DB::transaction(function () use ($ticket, $validated) {
            $lockedTicket = TripTicket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            abort_if($lockedTicket->status !== 'approved', 403);

            $existing = TravelOrder::where('trip_ticket_id', $lockedTicket->id)->first();
            if ($existing) {
                return $existing;
            }

            $to = TravelOrder::create([
                ...$validated,
                'trip_ticket_id' => $lockedTicket->getKey(),
                'status' => 'draft',
                'issued_by' => auth()->id(),
            ]);
            $issuedToUser = User::findOrFail($validated['issued_to']);
            $to->passengers()->createMany(
                TravelOrderPersonnel::mergeIssuedTo($validated['passengers'] ?? [], $issuedToUser)
            );
            TravelOrderLog::create([
                'travel_order_id' => $to->getKey(),
                'action' => 'generated_from_ticket',
                'changed_by' => auth()->id(),
                'remarks' => "Generated from Trip Ticket {$lockedTicket->ticket_number}.",
            ]);

            return $to;
        }, 3);

        return redirect()->route('travel-orders.show', $to->travel_order_number)
            ->with('success', "Travel Order created from {$ticket->ticket_number}.");
    }
}

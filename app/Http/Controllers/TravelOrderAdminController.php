<?php

namespace App\Http\Controllers;

use App\Models\TravelOrder;
use App\Models\TravelOrderLog;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\TravelOrderIssuedNotification;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TravelOrderAdminController extends Controller
{
    public function issue(TravelOrder $travelOrder): RedirectResponse
    {
        abort_if($travelOrder->status !== 'draft', 403);

        $travelOrder->update([
            'status'    => 'issued',
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
                'purpose'    => $ticket->purpose,
                'date_start' => $ticket->date_start->format('Y-m-d'),
                'date_end'   => $ticket->date_end->format('Y-m-d'),
                'passengers' => $passengers->values()->all(),
            ],
            'users'               => User::orderBy('name', 'asc')->get(['id', 'name']),
            'vehicles'            => Vehicle::where('is_active', '=', true)->get(['id', 'name', 'plate_number']),
            'transportationModes' => TravelOrder::TRANSPORTATION_MODES,
        ]);
    }

    public function generate(TripTicket $ticket): RedirectResponse
    {
        abort_if($ticket->status !== 'approved', 403, 'Travel Orders can only be generated from approved reservations.');

        if ($ticket->travelOrder) {
            return redirect()->route('travel-orders.show', $ticket->travelOrder->travel_order_number)
                             ->with('info', "A Travel Order already exists for this reservation.");
        }

        $validated = request()->validate([
            'issued_to'                      => ['required', 'exists:users,id'],
            'purpose'                        => ['required', 'string', 'max:1000'],
            'destination'                    => ['required', 'string', 'max:255'],
            'destination_scope'              => ['required', 'in:within_sdn,outside_sdn'],
            'date_start'                     => ['required', 'date'],
            'date_end'                       => ['required', 'date', 'after_or_equal:date_start'],
            'time_departure'                 => ['nullable', 'date_format:H:i'],
            'time_return'                    => ['nullable', 'date_format:H:i'],
            'transportation_mode'            => ['required', 'in:' . implode(',', array_keys(TravelOrder::TRANSPORTATION_MODES))],
            'vehicle_id'                     => ['nullable', 'exists:vehicles,id', 'required_if:transportation_mode,government_vehicle'],
            'fund_source'                    => ['nullable', 'string', 'max:255'],
            'expense_actual'                 => ['nullable', 'boolean'],
            'expense_per_diem'               => ['nullable', 'boolean'],
            'expense_per_diem_accommodation' => ['nullable', 'boolean'],
            'expense_per_diem_subsistence'   => ['nullable', 'boolean'],
            'expense_per_diem_incidental'    => ['nullable', 'boolean'],
            'expense_transportation'                      => ['nullable', 'boolean'],
            'expense_transportation_official_vehicle'     => ['nullable', 'boolean'],
            'expense_transportation_public_conveyance'    => ['nullable', 'boolean'],
            'expense_transportation_others'               => ['nullable', 'boolean'],
            'approving_officer'              => ['required', 'string', 'max:255'],
            'approving_position'             => ['required', 'string', 'max:255'],
            'remarks'                        => ['nullable', 'string', 'max:2000'],
            'passengers'                     => ['nullable', 'array'],
            'passengers.*.name'              => ['required', 'string', 'max:255'],
            'passengers.*.designation'       => ['nullable', 'string', 'max:255'],
            'passengers.*.user_id'           => ['nullable', 'exists:users,id'],
        ]);

        $to = TravelOrder::create([
            ...$validated,
            'trip_ticket_id' => $ticket->getKey(),
            'status'         => 'draft',
            'issued_by'      => auth()->id(),
        ]);

        $issuedToUser  = User::find($validated['issued_to']);
        $rawPassengers = $validated['passengers'] ?? [];
        $alreadyListed = collect($rawPassengers)->contains(
            fn ($p) => isset($p['user_id']) && (int) $p['user_id'] === $issuedToUser->id
        );
        $passengers = $alreadyListed
            ? $rawPassengers
            : array_merge(
                [['name' => $issuedToUser->name, 'designation' => null, 'user_id' => $issuedToUser->id]],
                $rawPassengers
            );

        $to->passengers()->createMany($passengers);

        // Sync issued_to into the linked trip ticket's passengers if not already listed
        $ticket->loadMissing('passengers');
        $existingNames = $ticket->passengers->pluck('name')->map(fn ($n) => strtolower(trim($n)));
        if (! $existingNames->contains(strtolower(trim($issuedToUser->name)))) {
            $ticket->passengers()->create([
                'name'        => $issuedToUser->name,
                'designation' => null,
            ]);
        }

        TravelOrderLog::create([
            'travel_order_id' => $to->getKey(),
            'action'          => 'generated_from_ticket',
            'changed_by'      => auth()->id(),
            'remarks'         => "Generated from Trip Ticket {$ticket->ticket_number}.",
        ]);

        return redirect()->route('travel-orders.show', $to->travel_order_number)
                         ->with('success', "Travel Order created from {$ticket->ticket_number}.");
    }
}

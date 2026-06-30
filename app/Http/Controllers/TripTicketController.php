<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTripTicketRequest;
use App\Models\TripTicket;
use App\Models\TripTicketLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\ReservationUpdatedNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class TripTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,approved,disapproved,completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $user = $request->user();
        $query = TripTicket::with(['requester', 'vehicle'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('requested_by', $user->id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from, fn ($q) => $q->where('date_start', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('date_start', '<=', $request->to))
            ->latest('date_start');

        $tickets = $query->paginate(15)->through(fn ($t) => [
            'ticket_number' => $t->ticket_number,
            'travel_date_label' => $t->travelDateLabel(),
            'is_multi_day' => $t->isMultiDay(),
            'destination' => $t->destination,
            'status' => $t->status,
            'date_filed' => $t->date_filed->format('Y-m-d'),
            'requester_name' => $t->requester->name,
        ]);

        return Inertia::render('Reservations/Index', [
            'tickets' => $tickets,
            'filters' => $request->only('status', 'from', 'to'),
        ]);
    }

    public function create(): Response
    {
        $vehicle = Vehicle::getActive();

        return Inertia::render('Reservations/Create', [
            'vehicle' => $vehicle->only('name', 'plate_number'),
        ]);
    }

    public function store(SaveTripTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = DB::transaction(function () use ($validated, $request) {
            $ticket = TripTicket::create([
                ...$validated,
                'date_of_travel' => $validated['date_start'],
                'status' => 'pending',
                'requested_by' => $request->user()->id,
            ]);
            $ticket->passengers()->createMany($validated['passengers']);

            return $ticket;
        });

        return redirect()->route('reservations.show', $ticket->ticket_number)
            ->with('success', 'Reservation filed successfully.');
    }

    public function show(TripTicket $ticket): Response
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['requester', 'approver', 'vehicle', 'passengers', 'travelOrder']);

        return Inertia::render('Reservations/Show', [
            'ticket' => array_merge($ticket->toArray(), [
                'travel_date_label' => $ticket->travelDateLabel(),
                'is_multi_day' => $ticket->isMultiDay(),
                'vehicle' => $ticket->vehicle->only('name', 'plate_number'),
                'travel_order_number' => $ticket->travelOrder?->travel_order_number,
            ]),
            'logs' => $ticket->logs()->reorder('created_at', 'desc')->with('actor')->get()->map(fn ($l) => [
                'id' => $l->id,
                'from_status' => $l->from_status,
                'to_status' => $l->to_status,
                'remarks' => $l->remarks,
                'created_at' => $l->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A'),
                'actor' => ['name' => $l->actor->name],
            ]),
        ]);
    }

    public function edit(TripTicket $ticket): Response
    {
        Gate::authorize('update', $ticket);

        $ticket->load(['vehicle', 'passengers']);
        $vehicle = $ticket->vehicle ?? Vehicle::getActive();

        return Inertia::render('Reservations/Edit', [
            'ticket' => array_merge(
                $ticket->only('ticket_number', 'purpose', 'destination', 'driver_name', 'time_departure', 'time_return', 'status'),
                [
                    'date_start' => $ticket->date_start?->format('Y-m-d'),
                    'date_end' => $ticket->date_end?->format('Y-m-d'),
                    'passengers' => $ticket->passengers->map->only('name', 'designation')->all(),
                ]
            ),
            'vehicle' => $vehicle->only('name', 'plate_number'),
        ]);
    }

    public function update(SaveTripTicketRequest $request, TripTicket $ticket): RedirectResponse
    {
        $user = auth()->user();

        Gate::authorize('update', $ticket);

        $validated = $request->validated();

        // Capture old values for descriptive log BEFORE updating
        $ticket->load('passengers');
        $oldDateLabel = $ticket->travelDateLabel();
        $oldPurpose = $ticket->purpose;
        $oldDestination = $ticket->destination;
        $oldDriver = $ticket->driver_name;
        $oldDeparture = $ticket->time_departure;
        $oldReturn = $ticket->time_return;
        $oldPassengerNames = $ticket->passengers->pluck('name')->sort()->values()->toArray();

        // Build descriptive change summary
        $changes = [];

        if ($oldPurpose !== $validated['purpose']) {
            $changes[] = 'Purpose updated.';
        }

        if ($oldDestination !== $validated['destination']) {
            $changes[] = "Destination changed from {$oldDestination} to {$validated['destination']}.";
        }

        if ($oldDriver !== $validated['driver_name']) {
            $changes[] = 'Driver changed from '.($oldDriver ?: '—').' to '.($validated['driver_name'] ?: '—').'.';
        }

        $newDateLabel = $this->formatDateRange($validated['date_start'], $validated['date_end']);
        if ($oldDateLabel !== $newDateLabel) {
            $changes[] = "Travel schedule changed from {$oldDateLabel} to {$newDateLabel}.";
        }

        if ($oldDeparture !== $validated['time_departure']) {
            $changes[] = 'Departure time changed from '.($oldDeparture ?? '—').' to '.($validated['time_departure'] ?? '—').'.';
        }

        if ($oldReturn !== $validated['time_return']) {
            $changes[] = 'Return time changed from '.($oldReturn ?? '—').' to '.($validated['time_return'] ?? '—').'.';
        }

        $newPassengerNames = collect($validated['passengers'])->pluck('name')->sort()->values()->toArray();
        if ($oldPassengerNames !== $newPassengerNames) {
            $changes[] = 'Passenger list updated.';
        }

        $summary = ! empty($changes) ? implode(' ', $changes) : null;

        DB::transaction(function () use ($ticket, $validated, $summary, $user) {
            $ticket->update([
                'purpose' => $validated['purpose'],
                'destination' => $validated['destination'],
                'driver_name' => $validated['driver_name'],
                'date_start' => $validated['date_start'],
                'date_end' => $validated['date_end'],
                'date_of_travel' => $validated['date_start'],
                'time_departure' => $validated['time_departure'],
                'time_return' => $validated['time_return'],
            ]);

            $ticket->passengers()->delete();
            $ticket->passengers()->createMany($validated['passengers']);

            if ($summary) {
                TripTicketLog::create([
                    'trip_ticket_id' => $ticket->id,
                    'from_status' => null,
                    'to_status' => 'edited',
                    'changed_by' => $user->id,
                    'remarks' => $summary,
                ]);
            }
        });

        if ($summary) {
            $ticket->loadMissing('requester');
            $notification = new ReservationUpdatedNotification($ticket, $user, $summary);

            if ($user->isAdmin() && $ticket->requested_by !== $user->id) {
                $ticket->requester?->notify($notification);
            } elseif (! $user->isAdmin()) {
                Notification::send(User::where('role', 'admin')->get(), $notification);
            }
        }

        return redirect()->route('reservations.show', $ticket->ticket_number)
            ->with('success', 'Reservation updated successfully.');
    }

    private function formatDateRange(string $start, string $end): string
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);

        return $s->ne($e)
            ? $s->format('M d').' – '.$e->format('M d, Y')
            : $s->format('M d, Y');
    }

    public function cancel(TripTicket $ticket): RedirectResponse
    {
        Gate::authorize('cancel', $ticket);

        if ($ticket->travelOrder()->where('status', '!=', 'cancelled')->exists()) {
            return back()->withErrors([
                'status' => 'Cancel the linked Travel Order before cancelling this reservation.',
            ]);
        }

        $ticket->update(['status' => 'cancelled']);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation cancelled.');
    }
}

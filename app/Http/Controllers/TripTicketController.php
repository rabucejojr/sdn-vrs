<?php

namespace App\Http\Controllers;

use App\Models\TripTicket;
use App\Models\TripTicketLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\ReservationUpdatedNotification;
use App\Rules\NoDateConflict;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class TripTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $query = TripTicket::with(['requester', 'vehicle'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('requested_by', $user->id))
            ->when($request->status,  fn ($q) => $q->where('status', $request->status))
            ->when($request->from,    fn ($q) => $q->where('date_start', '>=', $request->from))
            ->when($request->to,      fn ($q) => $q->where('date_start', '<=', $request->to))
            ->latest('date_start');

        $tickets = $query->paginate(15)->through(fn ($t) => [
            'ticket_number'     => $t->ticket_number,
            'travel_date_label' => $t->travelDateLabel(),
            'is_multi_day'      => $t->isMultiDay(),
            'destination'       => $t->destination,
            'status'            => $t->status,
            'date_filed'        => $t->date_filed->format('Y-m-d'),
            'requester_name'    => $t->requester->name,
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purpose'                   => ['required', 'string', 'max:1000'],
            'date_start'                => ['required', 'date', 'after_or_equal:today',
                                           new NoDateConflict($request->input('date_end') ?: $request->input('date_start'))],
            'date_end'                  => ['required', 'date', 'after_or_equal:date_start'],
            'time_departure'            => ['nullable', 'date_format:H:i'],
            'time_return'               => ['nullable', 'date_format:H:i'],
            'destination'               => ['required', 'string', 'max:255'],
            'passengers'                => ['required', 'array', 'min:1'],
            'passengers.*.name'         => ['required', 'string', 'max:255'],
            'passengers.*.designation'  => ['nullable', 'string', 'max:255'],
        ]);

        $ticket = TripTicket::create([
            ...$validated,
            'date_of_travel' => $validated['date_start'],
            'status'         => 'pending',
            'requested_by'   => $request->user()->id,
        ]);

        $ticket->passengers()->createMany($validated['passengers']);

        return redirect()->route('reservations.show', $ticket->ticket_number)
                         ->with('success', 'Reservation filed successfully.');
    }

    public function show(TripTicket $ticket): Response
    {
        $user = auth()->user();

        if (! $user->isAdmin() && $ticket->requested_by !== $user->id) {
            abort(403);
        }

        $ticket->load(['requester', 'approver', 'vehicle', 'passengers']);

        return Inertia::render('Reservations/Show', [
            'ticket' => array_merge($ticket->toArray(), [
                'travel_date_label' => $ticket->travelDateLabel(),
                'is_multi_day'      => $ticket->isMultiDay(),
                'vehicle'           => $ticket->vehicle->only('name', 'plate_number'),
            ]),
            'logs' => $ticket->logs()->with('actor')->get()->map(fn ($l) => [
                'id'          => $l->id,
                'from_status' => $l->from_status,
                'to_status'   => $l->to_status,
                'remarks'     => $l->remarks,
                'created_at'  => $l->created_at->format('M d, Y h:i A'),
                'actor'       => ['name' => $l->actor->name],
            ]),
        ]);
    }

    public function edit(TripTicket $ticket): Response
    {
        $user = auth()->user();

        if (! $user->isAdmin() && $ticket->requested_by !== $user->id) {
            abort(403);
        }

        $editableStatuses = $user->isAdmin()
            ? ['pending', 'approved', 'disapproved']
            : ['pending'];

        if (! in_array($ticket->status, $editableStatuses)) {
            abort(403, 'You do not have permission to edit this reservation.');
        }

        $ticket->load(['vehicle', 'passengers']);
        $vehicle = $ticket->vehicle ?? Vehicle::getActive();

        return Inertia::render('Reservations/Edit', [
            'ticket'  => array_merge(
                $ticket->only('ticket_number', 'purpose', 'destination', 'time_departure', 'time_return', 'status'),
                [
                    'date_start' => $ticket->date_start?->format('Y-m-d'),
                    'date_end'   => $ticket->date_end?->format('Y-m-d'),
                    'passengers' => $ticket->passengers->map->only('name', 'designation')->all(),
                ]
            ),
            'vehicle' => $vehicle->only('name', 'plate_number'),
        ]);
    }

    public function update(Request $request, TripTicket $ticket): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isAdmin() && $ticket->requested_by !== $user->id) {
            abort(403);
        }

        $editableStatuses = $user->isAdmin()
            ? ['pending', 'approved', 'disapproved']
            : ['pending'];

        if (! in_array($ticket->status, $editableStatuses)) {
            abort(403, 'You do not have permission to edit this reservation.');
        }

        $validated = $request->validate([
            'purpose'                   => ['required', 'string', 'max:1000'],
            'date_start'                => ['required', 'date',
                                           new NoDateConflict($request->input('date_end') ?: $request->input('date_start'), $ticket->id)],
            'date_end'                  => ['required', 'date', 'after_or_equal:date_start'],
            'time_departure'            => ['nullable', 'date_format:H:i'],
            'time_return'               => ['nullable', 'date_format:H:i'],
            'destination'               => ['required', 'string', 'max:255'],
            'passengers'                => ['required', 'array', 'min:1'],
            'passengers.*.name'         => ['required', 'string', 'max:255'],
            'passengers.*.designation'  => ['nullable', 'string', 'max:255'],
        ]);

        // Capture old values for descriptive log BEFORE updating
        $ticket->load('passengers');
        $oldDateLabel      = $ticket->travelDateLabel();
        $oldDestination    = $ticket->destination;
        $oldDeparture      = $ticket->time_departure;
        $oldReturn         = $ticket->time_return;
        $oldPassengerNames = $ticket->passengers->pluck('name')->sort()->values()->toArray();

        $ticket->update([
            'purpose'        => $validated['purpose'],
            'destination'    => $validated['destination'],
            'date_start'     => $validated['date_start'],
            'date_end'       => $validated['date_end'],
            'date_of_travel' => $validated['date_start'],
            'time_departure' => $validated['time_departure'],
            'time_return'    => $validated['time_return'],
        ]);

        $ticket->passengers()->delete();
        $ticket->passengers()->createMany($validated['passengers']);

        // Build descriptive change summary
        $changes = [];

        if ($ticket->purpose !== $validated['purpose']) {
            $changes[] = 'Purpose updated.';
        }

        if ($oldDestination !== $validated['destination']) {
            $changes[] = "Destination changed from {$oldDestination} to {$validated['destination']}.";
        }

        $newDateLabel = $this->formatDateRange($validated['date_start'], $validated['date_end']);
        if ($oldDateLabel !== $newDateLabel) {
            $changes[] = "Travel schedule changed from {$oldDateLabel} to {$newDateLabel}.";
        }

        if ($oldDeparture !== $validated['time_departure']) {
            $changes[] = 'Departure time changed from ' . ($oldDeparture ?? '—') . ' to ' . ($validated['time_departure'] ?? '—') . '.';
        }

        if ($oldReturn !== $validated['time_return']) {
            $changes[] = 'Return time changed from ' . ($oldReturn ?? '—') . ' to ' . ($validated['time_return'] ?? '—') . '.';
        }

        $newPassengerNames = collect($validated['passengers'])->pluck('name')->sort()->values()->toArray();
        if ($oldPassengerNames !== $newPassengerNames) {
            $changes[] = 'Passenger list updated.';
        }

        if (! empty($changes)) {
            $summary = implode(' ', $changes);

            TripTicketLog::create([
                'trip_ticket_id' => $ticket->id,
                'from_status'    => null,
                'to_status'      => 'edited',
                'changed_by'     => $user->id,
                'remarks'        => $summary,
            ]);

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
            ? $s->format('M d') . ' – ' . $e->format('M d, Y')
            : $s->format('M d, Y');
    }

    public function cancel(TripTicket $ticket): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isAdmin() && $ticket->requested_by !== $user->id) {
            abort(403);
        }

        if ($ticket->status !== 'pending') {
            abort(403, 'Only pending reservations can be cancelled.');
        }

        $ticket->update(['status' => 'cancelled']);

        return redirect()->route('reservations.index')
                         ->with('success', 'Reservation cancelled.');
    }
}

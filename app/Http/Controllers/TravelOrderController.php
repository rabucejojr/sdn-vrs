<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderRequest;
use App\Models\TravelOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\TravelOrderPersonnel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TravelOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate([
            'status' => ['nullable', 'in:draft,issued,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $user = $request->user();
        $query = TravelOrder::with(['issuedTo', 'issuedBy'])
            ->when(! $user->isAdmin(), function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('issued_to', $user->id)
                        ->orWhereHas('passengers', fn ($p) => $p->where('user_id', $user->id));
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from, fn ($q) => $q->where('date_start', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('date_start', '<=', $request->to))
            ->latest();

        $orders = $query->paginate(15)->through(fn ($to) => [
            'travel_order_number' => $to->travel_order_number,
            'destination' => $to->destination,
            'destination_scope' => $to->destination_scope,
            'travel_date_label' => $to->travelDateLabel(),
            'is_multi_day' => $to->isMultiDay(),
            'status' => $to->status,
            'issued_to_name' => $to->issuedTo->name,
            'issued_at' => $to->issued_at?->format('M d, Y'),
        ]);

        return Inertia::render('TravelOrders/Index', [
            'orders' => $orders,
            'filters' => $request->only('status', 'from', 'to'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/TravelOrders/Create', [
            'users' => User::orderBy('name', 'asc')->get(['id', 'name']),
            'vehicles' => Vehicle::where('is_active', '=', true)->get(['id', 'name', 'plate_number']),
            'transportationModes' => TravelOrder::TRANSPORTATION_MODES,
        ]);
    }

    public function store(StoreTravelOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $to = DB::transaction(function () use ($validated, $request) {
            $to = TravelOrder::create([
                ...$validated,
                'status' => 'draft',
                'issued_by' => $request->user()->id,
            ]);
            $issuedToUser = User::findOrFail($validated['issued_to']);
            $to->passengers()->createMany(
                TravelOrderPersonnel::mergeIssuedTo($validated['passengers'] ?? [], $issuedToUser)
            );

            return $to;
        });

        return redirect()->route('travel-orders.show', $to->travel_order_number)
            ->with('success', 'Travel Order created as draft.');
    }

    public function show(TravelOrder $travelOrder): Response
    {
        $user = request()->user();
        Gate::authorize('view', $travelOrder);

        $travelOrder->load(['issuedTo', 'issuedBy', 'vehicle', 'passengers', 'tripTicket']);

        return Inertia::render('TravelOrders/Show', [
            'order' => array_merge($travelOrder->toArray(), [
                'travel_date_label' => $travelOrder->travelDateLabel(),
                'is_multi_day' => $travelOrder->isMultiDay(),
                'is_outside_sdn' => $travelOrder->isOutsideSdn(),
                'transportation_label' => $travelOrder->transportationModeLabel(),
                'issued_at_formatted' => $travelOrder->issued_at?->format('F j, Y'),
                'trip_ticket_number' => $travelOrder->tripTicket
                    && ($user->isAdmin() || $travelOrder->tripTicket->requested_by === $user->id)
                        ? $travelOrder->tripTicket->ticket_number
                        : null,
                'source_is_newer' => $travelOrder->tripTicket
                    && $travelOrder->tripTicket->updated_at->gt($travelOrder->created_at),
            ]),
            'logs' => $travelOrder->logs()->with('actor')->get()->map(fn ($l) => [
                'id' => $l->id,
                'action' => $l->action,
                'remarks' => $l->remarks,
                'created_at' => $l->created_at->setTimezone('Asia/Manila')->format('M d, Y g:i A'),
                'actor' => ['name' => $l->actor->name],
            ]),
        ]);
    }

    public function edit(TravelOrder $travelOrder): Response
    {
        abort_if($travelOrder->status !== 'draft', 403, 'Only draft Travel Orders can be edited.');

        $travelOrder->load(['passengers']);

        return Inertia::render('Admin/TravelOrders/Edit', [
            'order' => array_merge(
                $travelOrder->only(
                    'travel_order_number', 'issued_to', 'purpose', 'destination',
                    'destination_scope', 'time_departure', 'time_return',
                    'transportation_mode', 'vehicle_id', 'fund_source',
                    'expense_actual', 'expense_per_diem', 'expense_per_diem_accommodation',
                    'expense_per_diem_subsistence', 'expense_per_diem_incidental',
                    'expense_transportation', 'expense_transportation_official_vehicle',
                    'expense_transportation_public_conveyance', 'expense_transportation_others',
                    'approving_officer', 'approving_position', 'remarks'
                ),
                [
                    'date_start' => $travelOrder->date_start->format('Y-m-d'),
                    'date_end' => $travelOrder->date_end->format('Y-m-d'),
                    'passengers' => $travelOrder->passengers->map->only('name', 'designation', 'user_id')->all(),
                ]
            ),
            'users' => User::orderBy('name', 'asc')->get(['id', 'name']),
            'vehicles' => Vehicle::where('is_active', '=', true)->get(['id', 'name', 'plate_number']),
            'transportationModes' => TravelOrder::TRANSPORTATION_MODES,
        ]);
    }

    public function update(UpdateTravelOrderRequest $request, TravelOrder $travelOrder): RedirectResponse
    {
        abort_if($travelOrder->status !== 'draft', 403, 'Only draft Travel Orders can be edited.');

        $validated = $request->validated();

        DB::transaction(function () use ($travelOrder, $validated) {
            $travelOrder->update($validated);
            $issuedToUser = User::findOrFail($validated['issued_to']);
            $passengers = TravelOrderPersonnel::mergeIssuedTo($validated['passengers'] ?? [], $issuedToUser);
            $travelOrder->passengers()->delete();
            $travelOrder->passengers()->createMany($passengers);
        });

        return redirect()->route('travel-orders.show', $travelOrder->travel_order_number)
            ->with('success', 'Travel Order updated.');
    }
}

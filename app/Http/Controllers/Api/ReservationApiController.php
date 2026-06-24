<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TripTicket::with(['requester:id,name,email', 'approver:id,name', 'vehicle:id,name,plate_number'])
            ->when($request->status,      fn ($q) => $q->where('status', $request->status))
            ->when($request->date_start,  fn ($q) => $q->where('date_start', '>=', $request->date_start))
            ->when($request->date_end,    fn ($q) => $q->where('date_end',   '<=', $request->date_end))
            ->orderBy('date_start', 'desc');

        $tickets = $query->paginate(20)->through(fn ($t) => [
            'ticket_number'     => $t->ticket_number,
            'date_filed'        => $t->date_filed?->toDateString(),
            'status'            => $t->status,
            'purpose'           => $t->purpose,
            'destination'       => $t->destination,
            'date_start'        => $t->date_start?->toDateString(),
            'date_end'          => $t->date_end?->toDateString(),
            'travel_date_label' => $t->travelDateLabel(),
            'is_multi_day'      => $t->isMultiDay(),
            'time_departure'    => $t->time_departure,
            'time_return'       => $t->time_return,
            'requester'         => $t->requester?->only('id', 'name', 'email'),
            'approver'          => $t->approver?->only('id', 'name'),
            'vehicle'           => $t->vehicle?->only('id', 'name', 'plate_number'),
            'remarks'           => $t->remarks,
        ]);

        return response()->json($tickets);
    }

    public function show(TripTicket $ticket): JsonResponse
    {
        $ticket->load([
            'requester:id,name,email',
            'approver:id,name',
            'vehicle:id,name,plate_number',
            'passengers',
            'logs.actor:id,name',
        ]);

        return response()->json([
            'ticket_number'     => $ticket->ticket_number,
            'date_filed'        => $ticket->date_filed?->toDateString(),
            'status'            => $ticket->status,
            'purpose'           => $ticket->purpose,
            'destination'       => $ticket->destination,
            'date_start'        => $ticket->date_start?->toDateString(),
            'date_end'          => $ticket->date_end?->toDateString(),
            'travel_date_label' => $ticket->travelDateLabel(),
            'is_multi_day'      => $ticket->isMultiDay(),
            'time_departure'    => $ticket->time_departure,
            'time_return'       => $ticket->time_return,
            'requester'         => $ticket->requester?->only('id', 'name', 'email'),
            'approver'          => $ticket->approver?->only('id', 'name'),
            'vehicle'           => $ticket->vehicle?->only('id', 'name', 'plate_number'),
            'passengers'        => $ticket->passengers->map->only('name', 'designation'),
            'remarks'           => $ticket->remarks,
            'logs'              => $ticket->logs->map(fn ($l) => [
                'from_status' => $l->from_status,
                'to_status'   => $l->to_status,
                'actor'       => $l->actor?->only('id', 'name'),
                'remarks'     => $l->remarks,
                'created_at'  => $l->created_at->toIso8601String(),
            ]),
        ]);
    }
}

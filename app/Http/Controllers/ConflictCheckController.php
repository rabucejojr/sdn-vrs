<?php

namespace App\Http\Controllers;

use App\Models\TripTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConflictCheckController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'date_start' => ['required', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
        ]);

        $dateStart = $request->query('date_start');
        $dateEnd = $request->query('date_end', $dateStart);

        // Resolve optional ticket_number exclusion (used during edits)
        $excludeTicketNumber = $request->query('exclude');
        $excludeId = $excludeTicketNumber
            ? TripTicket::where('ticket_number', $excludeTicketNumber)->value('id')
            : null;

        $conflict = TripTicket::conflicting($dateStart, $dateEnd, $excludeId)
            ->first(['ticket_number']);

        return response()->json([
            'conflict' => (bool) $conflict,
            'ticket' => $conflict?->ticket_number,
        ]);
    }
}

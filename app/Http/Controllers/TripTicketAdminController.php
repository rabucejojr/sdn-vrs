<?php

namespace App\Http\Controllers;

use App\Models\TripTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripTicketAdminController extends Controller
{
    public function approve(Request $request, TripTicket $ticket): RedirectResponse
    {
        $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($ticket, $request) {
            $locked = TripTicket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->status !== 'pending', 403, 'Only pending reservations can be approved.');

            $conflict = TripTicket::conflicting(
                $locked->date_start->toDateString(),
                $locked->date_end->toDateString(),
                $locked->id,
                $locked->vehicle_id,
            )->lockForUpdate()->first();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'date_start' => "Cannot approve: dates overlap {$conflict->ticket_number}.",
                ]);
            }

            $locked->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'remarks' => $request->remarks,
            ]);
        }, 3);

        return back()->with('success', 'Reservation approved.');
    }

    public function disapprove(Request $request, TripTicket $ticket): RedirectResponse
    {
        $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_if($ticket->status !== 'pending', 403, 'Only pending reservations can be disapproved.');

        $ticket->update([
            'status' => 'disapproved',
            'approved_by' => $request->user()->id,
            'remarks' => $request->remarks,
        ]);

        return back()->with('success', 'Reservation disapproved.');
    }

    public function complete(TripTicket $ticket): RedirectResponse
    {
        abort_if($ticket->status !== 'approved', 403, 'Only approved reservations can be marked complete.');

        $ticket->update(['status' => 'completed']);

        return back()->with('success', 'Reservation marked as completed.');
    }
}

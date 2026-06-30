<?php

namespace App\Http\Controllers;

use App\Models\TripTicket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TripTicketPrintController extends Controller
{
    public function print(TripTicket $ticket): View
    {
        Gate::authorize('print', $ticket);
        $ticket->load(['requester', 'approver', 'passengers', 'vehicle']);

        $mode = 'screen';
        $organization = config('organization');

        return view('trip-tickets.print', compact('ticket', 'mode', 'organization'));
    }

    public function pdf(TripTicket $ticket): Response
    {
        Gate::authorize('print', $ticket);
        $ticket->load(['requester', 'approver', 'passengers', 'vehicle']);

        $mode = 'pdf';
        $organization = config('organization');

        // 8.5 × 13 inches (Philippine long bond paper) in points at 72 pt/inch
        $pdf = Pdf::loadView('trip-tickets.print', compact('ticket', 'mode', 'organization'))
            ->setPaper([0, 0, 612, 936], 'landscape');

        return $pdf->download($ticket->formattedTicketNumber().'.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TripTicket;
use Barryvdh\DomPDF\Facade\Pdf;

class TripTicketPrintController extends Controller
{
    public function print(TripTicket $ticket): \Illuminate\View\View
    {
        $ticket->load(['requester', 'approver', 'passengers', 'vehicle']);

        $mode = 'screen';

        return view('trip-tickets.print', compact('ticket', 'mode'));
    }

    public function pdf(TripTicket $ticket): \Illuminate\Http\Response
    {
        $ticket->load(['requester', 'approver', 'passengers', 'vehicle']);

        $mode = 'pdf';

        // 8.5 × 13 inches (Philippine long bond paper) in points at 72 pt/inch
        $pdf = Pdf::loadView('trip-tickets.print', compact('ticket', 'mode'))
                  ->setPaper([0, 0, 612, 936], 'landscape');

        return $pdf->download("{$ticket->ticket_number}.pdf");
    }
}

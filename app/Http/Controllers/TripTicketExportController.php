<?php

namespace App\Http\Controllers;

use App\Exports\TripTicketsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TripTicketExportController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,approved,disapproved,completed,cancelled'],
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $filename = 'trip-tickets-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new TripTicketsExport(
                status: $request->query('status'),
                from:   $request->query('from'),
                to:     $request->query('to'),
            ),
            $filename,
        );
    }
}

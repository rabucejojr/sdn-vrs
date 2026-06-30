<?php

namespace App\Http\Controllers;

use App\Models\TravelOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TravelOrderPrintController extends Controller
{
    public function print(TravelOrder $travelOrder): View
    {
        Gate::authorize('print', $travelOrder);

        $travelOrder->load('passengers.user');

        return view('travel-orders.print', [
            'order' => $travelOrder,
            'mode' => 'screen',
            'organization' => config('organization'),
        ]);
    }

    public function pdf(TravelOrder $travelOrder): Response
    {
        Gate::authorize('print', $travelOrder);

        $travelOrder->load('passengers.user');

        $pdf = Pdf::loadView('travel-orders.print', [
            'order' => $travelOrder,
            'mode' => 'pdf',
            'organization' => config('organization'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($travelOrder->travel_order_number.'.pdf');
    }
}

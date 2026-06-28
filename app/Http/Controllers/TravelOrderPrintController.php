<?php

namespace App\Http\Controllers;

use App\Models\TravelOrder;
use App\Models\TravelOrderLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class TravelOrderPrintController extends Controller
{
    public function print(TravelOrder $travelOrder): \Illuminate\Contracts\View\View
    {
        $this->authorizeView($travelOrder);

        TravelOrderLog::create([
            'travel_order_id' => $travelOrder->id,
            'action'          => 'printed',
            'changed_by'      => auth()->id(),
        ]);

        $travelOrder->load('passengers.user');

        return view('travel-orders.print', [
            'order' => $travelOrder,
            'mode'  => 'screen',
        ]);
    }

    public function pdf(TravelOrder $travelOrder): Response
    {
        $this->authorizeView($travelOrder);

        TravelOrderLog::create([
            'travel_order_id' => $travelOrder->id,
            'action'          => 'pdf_downloaded',
            'changed_by'      => auth()->id(),
        ]);

        $travelOrder->load('passengers.user');

        $pdf = Pdf::loadView('travel-orders.print', [
            'order' => $travelOrder,
            'mode'  => 'pdf',
        ])->setPaper('a4', 'portrait');

        return $pdf->download($travelOrder->travel_order_number . '.pdf');
    }

    private function authorizeView(TravelOrder $travelOrder): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) return;

        $isPassenger = $travelOrder->passengers()->where('user_id', $user->id)->exists();

        if ($travelOrder->issued_to !== $user->id && ! $isPassenger) {
            abort(403);
        }
    }
}

<?php

namespace App\Observers;

use App\Models\TravelOrder;
use App\Models\TravelOrderLog;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class TravelOrderObserver implements ShouldHandleEventsAfterCommit
{
    public function created(TravelOrder $to): void
    {
        TravelOrderLog::create([
            'travel_order_id' => $to->id,
            'action' => 'created',
            'changed_by' => $to->issued_by,
        ]);
    }

    public function updated(TravelOrder $to): void
    {
        if ($to->wasChanged('status')) {
            $action = match ($to->status) {
                'issued' => 'issued',
                'cancelled' => 'cancelled',
                default => 'updated',
            };

            TravelOrderLog::create([
                'travel_order_id' => $to->id,
                'action' => $action,
                'changed_by' => auth()->id() ?? $to->issued_by,
                'remarks' => $to->remarks,
            ]);

            return;
        }

        // Non-status field changes
        $tracked = [
            'purpose', 'destination', 'destination_scope',
            'date_start', 'date_end', 'time_departure', 'time_return',
            'transportation_mode', 'vehicle_id', 'fund_source',
            'expense_actual', 'expense_per_diem', 'expense_per_diem_accommodation',
            'expense_per_diem_subsistence', 'expense_per_diem_incidental',
            'expense_transportation', 'approving_officer', 'approving_position',
        ];

        if ($to->wasChanged($tracked)) {
            TravelOrderLog::create([
                'travel_order_id' => $to->id,
                'action' => 'updated',
                'changed_by' => auth()->id() ?? $to->issued_by,
            ]);
        }
    }
}

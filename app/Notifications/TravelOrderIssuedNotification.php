<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TravelOrderIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected TravelOrder $travelOrder)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $number = $this->travelOrder->travel_order_number;

        return [
            'action' => 'travel_order_issued',
            'travel_order_number' => $number,
            'message' => "Travel Order {$number} has been issued for your travel to {$this->travelOrder->destination}.",
            'url' => '/travel-orders/'.$number,
        ];
    }
}

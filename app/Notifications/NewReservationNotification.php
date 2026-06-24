<?php

namespace App\Notifications;

use App\Models\TripTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly TripTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_number'  => $this->ticket->ticket_number,
            'requester_name' => $this->ticket->requester?->name ?? 'Unknown',
            'action'         => 'filed',
            'message'        => 'New reservation request from ' . ($this->ticket->requester?->name ?? 'Unknown'),
            'url'            => '/reservations/' . $this->ticket->ticket_number,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\TripTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly TripTicket $ticket,
        public readonly User $actor,
        public readonly string $changesSummary,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isRequester = $notifiable->id === $this->ticket->requested_by;

        $message = $isRequester
            ? "Your reservation {$this->ticket->ticket_number} has been updated by an administrator."
            : "Reservation {$this->ticket->ticket_number} has been updated by {$this->actor->name}.";

        return [
            'ticket_number'  => $this->ticket->ticket_number,
            'requester_name' => $this->ticket->requester?->name ?? 'Unknown',
            'action'         => 'updated',
            'message'        => $message,
            'remarks'        => $this->changesSummary,
            'url'            => '/reservations/' . $this->ticket->ticket_number,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\TripTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly TripTicket $ticket,
        public readonly string $fromStatus,
        public readonly string $toStatus,
        public readonly User $actor,
        public readonly ?string $remarks = null,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $ticket = $this->ticket->ticket_number;
        $isRequester = $notifiable->id === $this->ticket->requested_by;

        $message = match (true) {
            $isRequester && $this->toStatus === 'approved' => "Your reservation {$ticket} has been approved.",
            $isRequester && $this->toStatus === 'disapproved' => "Your reservation {$ticket} has been disapproved.",
            $isRequester && $this->toStatus === 'completed' => "Your reservation {$ticket} has been marked as completed.",
            $isRequester && $this->toStatus === 'cancelled' => "Your reservation {$ticket} has been cancelled.",
            // Admin receiving a cancellation by staff
            default => "Reservation {$ticket} was cancelled by ".($this->ticket->requester?->name ?? 'the requester').'.',
        };

        return [
            'ticket_number' => $ticket,
            'requester_name' => $this->ticket->requester?->name ?? 'Unknown',
            'action' => $this->toStatus,
            'message' => $message,
            'remarks' => $this->remarks,
            'url' => '/reservations/'.$ticket,
        ];
    }
}

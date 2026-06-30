<?php

namespace App\Policies;

use App\Models\TripTicket;
use App\Models\User;

class TripTicketPolicy
{
    public function view(User $user, TripTicket $ticket): bool
    {
        return $user->isAdmin() || $ticket->requested_by === $user->id;
    }

    public function update(User $user, TripTicket $ticket): bool
    {
        if (! $this->view($user, $ticket)) {
            return false;
        }

        return $user->isAdmin()
            ? in_array($ticket->status, ['pending', 'approved'], true)
            : $ticket->status === 'pending';
    }

    public function cancel(User $user, TripTicket $ticket): bool
    {
        return $this->view($user, $ticket)
            && in_array($ticket->status, ['pending', 'approved'], true);
    }

    public function print(User $user, TripTicket $ticket): bool
    {
        return $this->view($user, $ticket)
            && in_array($ticket->status, ['approved', 'completed'], true);
    }
}

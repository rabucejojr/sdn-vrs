<?php

namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;

class TravelOrderPolicy
{
    public function view(User $user, TravelOrder $travelOrder): bool
    {
        return $user->isAdmin()
            || $travelOrder->issued_to === $user->id
            || $travelOrder->passengers()->where('user_id', $user->id)->exists();
    }

    public function print(User $user, TravelOrder $travelOrder): bool
    {
        if (! $this->view($user, $travelOrder)) {
            return false;
        }

        return $travelOrder->status === 'issued'
            || ($user->isAdmin() && $travelOrder->status === 'draft');
    }
}

<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserDeactivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly User $actor)
    {
        $this->afterCommit();
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'action' => 'deactivated',
            'message' => "Your account has been deactivated by {$this->actor->name}. Contact an administrator.",
            'url' => '/login',
        ];
    }
}

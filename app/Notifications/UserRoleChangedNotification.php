<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserRoleChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $newRole,
        private readonly User $actor,
    ) {
        $this->afterCommit();
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'action' => 'role_changed',
            'message' => "Your role has been changed to {$this->newRole} by {$this->actor->name}.",
            'url' => '/dashboard',
        ];
    }
}

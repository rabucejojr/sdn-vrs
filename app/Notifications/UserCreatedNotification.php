<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly User $creator)
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
            'action' => 'account_created',
            'message' => "Your account was created by {$this->creator->name}.",
            'url' => '/dashboard',
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'action'  => 'password_changed',
            'message' => 'Your password was successfully changed.',
            'url'     => '/dashboard',
        ];
    }
}

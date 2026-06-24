<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class EmailVerifiedNotification extends Notification
{
    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'action'  => 'email_verified',
            'message' => 'Your email address has been successfully verified.',
            'url'     => '/dashboard',
        ];
    }
}

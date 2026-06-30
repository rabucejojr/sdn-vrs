<?php

namespace App\Providers;

use App\Models\TravelOrder;
use App\Models\TripTicket;
use App\Notifications\EmailVerifiedNotification;
use App\Notifications\PasswordChangedNotification;
use App\Observers\TravelOrderObserver;
use App\Observers\TripTicketObserver;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        TripTicket::observe(TripTicketObserver::class);
        TravelOrder::observe(TravelOrderObserver::class);

        Event::listen(Verified::class, function ($event) {
            try {
                $event->user->notify(new EmailVerifiedNotification);
            } catch (\Throwable $exception) {
                report($exception);
            }
        });

        Event::listen(PasswordReset::class, function ($event) {
            try {
                $event->user->notify(new PasswordChangedNotification);
            } catch (\Throwable $exception) {
                report($exception);
            }
        });
    }
}

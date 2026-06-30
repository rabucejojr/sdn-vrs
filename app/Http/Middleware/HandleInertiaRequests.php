<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            ],
            'notifications' => [
                'unread_count' => $request->user()?->unreadNotifications()->count() ?? 0,
            ],
            'organization' => config('organization'),
            'api' => [
                'token_expiration_days' => config('api.token_expiration_days'),
            ],
        ];
    }
}

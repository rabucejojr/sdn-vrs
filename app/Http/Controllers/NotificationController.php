<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse|Response
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        if (! $request->expectsJson()) {
            return Inertia::render('Notifications/Index', [
                'notifications' => $notifications,
            ]);
        }

        return response()->json($notifications);
    }

    public function poll(Request $request): JsonResponse
    {
        $request->validate(['after' => ['nullable', 'date']]);
        $user = auth()->user();
        $after = $request->query('after');

        $query = $user->notifications()->latest();

        if ($after) {
            $query->where('created_at', '>', $after);
        }

        $notifications = $query->get()->map(fn ($n) => [
            'id' => $n->id,
            'data' => $n->data,
            'read_at' => $n->read_at,
            'created_at' => $n->created_at->toIso8601String(),
        ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id): JsonResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['unread_count' => auth()->user()->unreadNotifications()->count()]);
    }

    public function markAllRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['unread_count' => 0]);
    }
}

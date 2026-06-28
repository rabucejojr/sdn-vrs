<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use App\Notifications\UserDeactivatedNotification;
use App\Notifications\UserRoleChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()
            ->leftJoin(DB::raw('(SELECT user_id, MAX(last_activity) as last_activity FROM sessions GROUP BY user_id) as sess'), 'users.id', '=', 'sess.user_id')
            ->select('users.*', 'sess.last_activity')
            ->withCount('tripTickets as reservation_count');

        if ($search = $request->query('search')) {
            $query->where(fn ($q) => $q
                ->where('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
            );
        }

        $sort = $request->query('sort', 'created_at_desc');
        match ($sort) {
            'name_asc'        => $query->orderBy('users.name'),
            'name_desc'       => $query->orderByDesc('users.name'),
            'role_asc'        => $query->orderBy('users.role')->orderBy('users.name'),
            'created_at_asc'  => $query->orderBy('users.created_at'),
            default           => $query->orderByDesc('users.created_at'),
        };

        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filters' => $request->only(['search', 'sort']),
            'stats'   => [
                'total'    => User::count(),
                'admins'   => User::where('role', 'admin')->count(),
                'staff'    => User::where('role', 'staff')->count(),
                'active'   => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'      => $request->name,
            'position'  => $request->position,
            'email'     => $request->email,
            'role'      => $request->role,
            'password'  => Hash::make(Str::password(32)),
            'is_active' => true,
        ]);

        Password::sendResetLink(['email' => $user->email]);
        $user->notify(new UserCreatedNotification(auth()->user()));

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Account created. A setup email has been sent to {$user->email}.");
    }

    public function show(User $user): Response
    {
        $lastActivity = DB::table('sessions')
            ->where('user_id', $user->id)
            ->max('last_activity');

        $tickets = $user->tripTickets()
            ->with('vehicle')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'tickets_page');

        $stats = [
            'total'       => $user->tripTickets()->count(),
            'pending'     => $user->tripTickets()->where('status', 'pending')->count(),
            'approved'    => $user->tripTickets()->where('status', 'approved')->count(),
            'completed'   => $user->tripTickets()->where('status', 'completed')->count(),
            'disapproved' => $user->tripTickets()->where('status', 'disapproved')->count(),
            'cancelled'   => $user->tripTickets()->where('status', 'cancelled')->count(),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user'         => array_merge($user->toArray(), [
                'last_activity' => $lastActivity,
            ]),
            'tickets'      => $tickets->through(fn ($t) => [
                'ticket_number'     => $t->ticket_number,
                'travel_date_label' => $t->travelDateLabel(),
                'is_multi_day'      => $t->isMultiDay(),
                'destination'       => $t->destination,
                'status'            => $t->status,
                'date_filed'        => $t->date_filed->format('M d, Y'),
            ]),
            'ticketStats'  => $stats,
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $oldRole  = $user->role;
        $oldEmail = $user->email;

        if ($request->role !== 'admin' || !$user->is_active) {
            $this->ensureNotLastAdmin($user);
        }

        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own administrator role.']);
        }

        $user->name     = $request->name;
        $user->position = $request->position;
        $user->email    = $request->email;
        $user->role     = $request->role;

        if ($oldEmail !== $request->email) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($oldRole !== $request->role) {
            $user->notify(new UserRoleChangedNotification($request->role, auth()->user()));
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['active' => 'You cannot deactivate your own account.']);
        }

        if ($user->is_active) {
            $this->ensureNotLastAdmin($user);
            $user->notify(new UserDeactivatedNotification(auth()->user()));
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$user->name} has been {$action}.");
    }

    private function ensureNotLastAdmin(User $user): void
    {
        if ($user->role !== 'admin') {
            return;
        }

        $remainingAdmins = User::where('role', 'admin')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->count();

        if ($remainingAdmins === 0) {
            abort(422, 'Cannot remove the last active administrator.');
        }
    }
}
<?php

use App\Http\Controllers\ConflictCheckController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TravelOrderAdminController;
use App\Http\Controllers\TravelOrderController;
use App\Http\Controllers\TravelOrderPrintController;
use App\Http\Controllers\TripTicketAdminController;
use App\Http\Controllers\TripTicketController;
use App\Http\Controllers\TripTicketExportController;
use App\Http\Controllers\TripTicketPrintController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

// ── Dashboard ────────────────────────────────────────────────────────────────
Route::get('/dashboard', DashboardController::class)
     ->middleware(['auth', 'verified'])
     ->name('dashboard');

// ── Auth user routes ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Profile
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reservations
    Route::get('/reservations',             [TripTicketController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create',      [TripTicketController::class, 'create'])->name('reservations.create');
    Route::post('/reservations',            [TripTicketController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{ticket}',    [TripTicketController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{ticket}/edit',   [TripTicketController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{ticket}',        [TripTicketController::class, 'update'])->name('reservations.update');
    Route::delete('/reservations/{ticket}',     [TripTicketController::class, 'cancel'])->name('reservations.cancel');

    // Print / PDF (Blade, no Inertia)
    Route::get('/reservations/{ticket}/print', [TripTicketPrintController::class, 'print'])->name('reservations.print');
    Route::get('/reservations/{ticket}/pdf',   [TripTicketPrintController::class, 'pdf'])->name('reservations.pdf');

    // Conflict check (JSON, called via axios)
    Route::get('/api/reservations/check-conflict', [ConflictCheckController::class, 'check'])
         ->name('api.reservations.check-conflict');

    // Travel Orders (all authenticated users)
    Route::get('/travel-orders',          [TravelOrderController::class, 'index'])->name('travel-orders.index');
    Route::get('/travel-orders/{travelOrder}',       [TravelOrderController::class, 'show'])->name('travel-orders.show');
    Route::get('/travel-orders/{travelOrder}/print', [TravelOrderPrintController::class, 'print'])->name('travel-orders.print');
    Route::get('/travel-orders/{travelOrder}/pdf',   [TravelOrderPrintController::class, 'pdf'])->name('travel-orders.pdf');

    // Notifications
    Route::get('/notifications',             [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll',        [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::patch('/notifications/read-all',  [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // ── Admin-only routes ────────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        // Travel Orders (admin-only management)
        // generate routes MUST come before {travelOrder} to avoid routing ambiguity
        Route::get('/travel-orders/create',                          [TravelOrderController::class, 'create'])->name('travel-orders.create');
        Route::post('/travel-orders',                                [TravelOrderController::class, 'store'])->name('travel-orders.store');
        Route::get('/travel-orders/generate/{ticket}',               [TravelOrderAdminController::class, 'generateForm'])->name('travel-orders.generate-form');
        Route::post('/travel-orders/generate/{ticket}',              [TravelOrderAdminController::class, 'generate'])->name('travel-orders.generate');
        Route::get('/travel-orders/{travelOrder}/edit',              [TravelOrderController::class, 'edit'])->name('travel-orders.edit');
        Route::put('/travel-orders/{travelOrder}',                   [TravelOrderController::class, 'update'])->name('travel-orders.update');
        Route::patch('/travel-orders/{travelOrder}/issue',           [TravelOrderAdminController::class, 'issue'])->name('travel-orders.issue');
        Route::patch('/travel-orders/{travelOrder}/cancel',          [TravelOrderAdminController::class, 'cancel'])->name('travel-orders.cancel');

        // Reservation actions
        Route::patch('/reservations/{ticket}/approve',    [TripTicketAdminController::class, 'approve'])->name('reservations.approve');
        Route::patch('/reservations/{ticket}/disapprove', [TripTicketAdminController::class, 'disapprove'])->name('reservations.disapprove');
        Route::patch('/reservations/{ticket}/complete',   [TripTicketAdminController::class, 'complete'])->name('reservations.complete');

        // Excel export
        Route::get('/reservations/export', TripTicketExportController::class)->name('reservations.export');

        // Vehicles
        Route::get('/vehicles',          [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehicles/create',   [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles',         [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}',      [VehicleController::class, 'update'])->name('vehicles.update');

        // Users
        Route::resource('users', UserController::class)->except(['destroy']);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });
});

require __DIR__.'/auth.php';

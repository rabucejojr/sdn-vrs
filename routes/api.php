<?php

use App\Http\Controllers\Api\ReservationApiController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\VehicleApiController;
use Illuminate\Support\Facades\Route;

// ── Token management (session-authenticated — web users create/revoke their own tokens) ──
Route::middleware(['auth:sanctum', 'active', 'verified'])->group(function () {

    // Create a new API token
    Route::post('/tokens/create', [TokenController::class, 'create'])->name('api.tokens.create');

    // List current user's tokens
    Route::get('/tokens', [TokenController::class, 'index'])->name('api.tokens.index');

    // Revoke a token by ID
    Route::delete('/tokens/{tokenId}', [TokenController::class, 'destroy'])->name('api.tokens.destroy');

    // ── Read-only data endpoints ──────────────────────────────────────────────
    Route::middleware('ability:reservations:read')->group(function () {
        Route::get('/reservations', [ReservationApiController::class, 'index'])->name('api.reservations.index');
        Route::get('/reservations/{ticket}', [ReservationApiController::class, 'show'])->name('api.reservations.show');
    });

    Route::middleware('ability:vehicles:read')->group(function () {
        Route::get('/vehicles', [VehicleApiController::class, 'index'])->name('api.vehicles.index');
        Route::get('/vehicles/{vehicle}', [VehicleApiController::class, 'show'])->name('api.vehicles.show');
    });
});

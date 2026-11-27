<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeController;
use Illuminate\Support\Facades\Route;

// Every route lives under /api and goes through the `web` group (session cookie + CSRF),
// because the SPA authenticates with a session cookie, not a token (ADR 0001).
Route::prefix('api')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok']));

    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');
        Route::get('/callback', [AuthController::class, 'callback'])->middleware('throttle:20,1');
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::middleware('auth')->group(function () {
        Route::get('/me', [MeController::class, 'show']);

        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::put('/users/{user}/admin', [AdminUserController::class, 'grantAdmin']);
            Route::delete('/users/{user}/admin', [AdminUserController::class, 'revokeAdmin']);
        });
    });
});

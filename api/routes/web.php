<?php

use Illuminate\Support\Facades\Route;

// Every route lives under /api and goes through the `web` group (session cookie + CSRF),
// because the SPA authenticates with a session cookie, not a token (ADR 0001).
Route::prefix('api')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok']));
});

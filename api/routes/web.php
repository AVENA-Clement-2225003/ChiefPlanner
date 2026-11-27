<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MealFlowController;
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
        Route::get('/catalog', [CatalogController::class, 'index']);

        Route::get('/ingredients', [IngredientController::class, 'index']);
        Route::post('/ingredients', [IngredientController::class, 'store']);
        Route::patch('/ingredients/{ingredient}', [IngredientController::class, 'update']);
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy']);

        Route::get('/dishes', [DishController::class, 'index']);
        Route::get('/dishes/shared', [DishController::class, 'shared']);
        Route::post('/dishes', [DishController::class, 'store']);
        Route::get('/dishes/{dish}', [DishController::class, 'show']);
        Route::put('/dishes/{dish}', [DishController::class, 'update']);
        Route::delete('/dishes/{dish}', [DishController::class, 'destroy']);
        Route::put('/dishes/{dish}/share', [DishController::class, 'share']);
        Route::put('/dishes/{dish}/subscription', [DishController::class, 'subscribe']);
        Route::delete('/dishes/{dish}/subscription', [DishController::class, 'unsubscribe']);

        Route::get('/meal-flows', [MealFlowController::class, 'index']);
        Route::post('/meal-flows', [MealFlowController::class, 'store']);
        Route::put('/meal-flows/{mealFlow}', [MealFlowController::class, 'update']);
        Route::delete('/meal-flows/{mealFlow}', [MealFlowController::class, 'destroy']);

        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::put('/users/{user}/admin', [AdminUserController::class, 'grantAdmin']);
            Route::delete('/users/{user}/admin', [AdminUserController::class, 'revokeAdmin']);
        });
    });
});

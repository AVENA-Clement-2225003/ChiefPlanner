<?php

use App\Http\Controllers\AdminIngredientController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MealFlowController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\SpendingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\WeekController;
use App\Local\DevLoginController;
use Illuminate\Support\Facades\Route;

// Every route lives under /api and goes through the `web` group (session cookie + CSRF),
// because the SPA authenticates with a session cookie, not a token (ADR 0001).
Route::prefix('api')->group(function () {
    Route::get('/health', HealthController::class);

    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');
        Route::get('/callback', [AuthController::class, 'callback'])->middleware('throttle:20,1');
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::middleware('auth')->group(function () {
        Route::get('/me', [MeController::class, 'show']);
        Route::delete('/me', [MeController::class, 'destroy']);
        Route::get('/catalog', [CatalogController::class, 'index']);

        Route::get('/ingredients', [IngredientController::class, 'index']);
        Route::post('/ingredients', [IngredientController::class, 'store']);

        Route::get('/dishes', [DishController::class, 'index']);
        Route::get('/dishes/shared', [DishController::class, 'shared']);
        Route::post('/dishes', [DishController::class, 'store']);
        Route::get('/dishes/{dish}', [DishController::class, 'show']);
        Route::put('/dishes/{dish}', [DishController::class, 'update']);
        Route::delete('/dishes/{dish}', [DishController::class, 'destroy']);
        Route::put('/dishes/{dish}/share', [DishController::class, 'share']);
        Route::put('/dishes/{dish}/subscription', [DishController::class, 'subscribe']);
        Route::delete('/dishes/{dish}/subscription', [DishController::class, 'unsubscribe']);

        Route::get('/preferences', [PreferencesController::class, 'show']);
        Route::put('/preferences', [PreferencesController::class, 'update']);

        Route::get('/week', [WeekController::class, 'show']);
        Route::put('/week', [WeekController::class, 'restore']);
        Route::delete('/week/meals/{day}/{slot}', [WeekController::class, 'clearMeal'])
            ->whereNumber('day')->whereIn('slot', ['lunch', 'dinner']);
        Route::post('/week/proposal', [WeekController::class, 'propose']);
        Route::post('/week/revert', [WeekController::class, 'revert']);
        Route::put('/week/meals/{day}/{slot}', [WeekController::class, 'updateMeal'])
            ->whereNumber('day')->whereIn('slot', ['lunch', 'dinner']);

        Route::get('/shopping-list', [ShoppingListController::class, 'show']);
        Route::put('/shopping-list/ticks', [ShoppingListController::class, 'tick']);
        Route::delete('/shopping-list/ticks', [ShoppingListController::class, 'clearTicks']);
        Route::post('/extras', [ExtraController::class, 'store']);
        Route::put('/extras/{extra}', [ExtraController::class, 'update']);
        Route::delete('/extras/{extra}', [ExtraController::class, 'destroy']);

        Route::get('/store', [StoreController::class, 'index']);
        Route::put('/store/{ingredient}', [StoreController::class, 'update']);

        Route::get('/spendings/summary', [SpendingController::class, 'summary']);
        Route::post('/spendings', [SpendingController::class, 'store']);
        Route::delete('/spendings/{spending}', [SpendingController::class, 'destroy']);

        Route::get('/meal-flows', [MealFlowController::class, 'index']);
        Route::post('/meal-flows', [MealFlowController::class, 'store']);
        Route::put('/meal-flows/{mealFlow}', [MealFlowController::class, 'update']);
        Route::delete('/meal-flows/{mealFlow}', [MealFlowController::class, 'destroy']);

        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::put('/users/{user}/admin', [AdminUserController::class, 'grantAdmin']);
            Route::delete('/users/{user}/admin', [AdminUserController::class, 'revokeAdmin']);
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

            Route::get('/ingredients', [AdminIngredientController::class, 'index']);
            Route::post('/ingredients/{ingredient}/merge', [AdminIngredientController::class, 'merge']);
            Route::delete('/ingredients/{ingredient}', [AdminIngredientController::class, 'destroy']);
        });
    });
});

// Local-only sign-in for working on the interface without Authentik. Never registered in production.
if (app()->environment('local')) {
    Route::get('/api/dev/login', DevLoginController::class);
}

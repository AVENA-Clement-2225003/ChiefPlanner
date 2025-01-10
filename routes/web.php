<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlatsController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\SemaineController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CreatorMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/debug')->group(function () {
    Route::get('/', [HomeController::class, 'showDebug'])->name('debug');
    Route::get('/reset', [HomeController::class, 'resetDebug'])->name('debug.reset');
});

Route::middleware(AuthMiddleware::class)->group(function() {
    Route::get('/', [HomeController::class, 'showHome'])->name('home');

    Route::get('/plats', [PlatsController::class, 'showPlats'])->name('plats');
    Route::get('/ingredients', [PlatsController::class, 'showIngredients'])->name('ingredients');
    Route::get('/refresh', [SemaineController::class, 'prepareAWeek'])->name('refresh');

    Route::prefix('/preferences')->group(function () {
        Route::get('/', [PreferenceController::class, 'showPreferences'])->name('preferences');
        Route::post('/update', [PreferenceController::class, 'updatePreferences'])->name('preferences.update');
    });

    Route::prefix('/add')->group(function () {
        Route::middleware(CreatorMiddleware::class)->group(function() {
            Route::post('/dish', [PlatsController::class, 'addDish'])->name('add.dish');
            Route::post('/ingredient', [PlatsController::class, 'addIngredient'])->name('add.ingredient');
        });
        Route::post('/groceries_purchase', [PlatsController::class, 'addGroceriesPurchase'])->name('add.groceries');
    });

    Route::middleware(AdminMiddleware::class)->group(function() {
        Route::prefix('/admin')->group(function () {
            Route::get('/', [AdminController::class, 'showDashboard'])->name('admin.dashboard');
            Route::prefix('/user/{user_id}')->group(function () {
                Route::get('/inspect', [AdminController::class, 'showUser'])->name('admin.user.inspect');
                Route::post('/change-role', [AdminController::class, 'changeRole'])->name('admin.user.changeRole');
                Route::post('/delete', [AdminController::class, 'deleteUser'])->name('admin.user.delete');
            });
        });
    });

    Route::prefix('/extra')->group(function () {
        Route::get('/', [ExtraController::class, 'showExtraEdit'])->name('extra.homepage');
        Route::post('/add', [ExtraController::class, 'addExtra'])->name('extra.add');
        Route::post('/delete', [ExtraController::class, 'deleteExtra'])->name('extra.delete');
    });
});

Route::prefix('/authentification')->group(function () {
    Route::get('/log-in', function () {return view('auth.login');})->name('auth.connection');
    Route::get('/sign-in', function () {return view('auth.signin');})->name('auth.inscription');
    Route::get('/log-out', [AuthController::class, 'logOut'])->name('auth.logout');
    Route::prefix('/process')->group(function () {
        Route::post('/inscription', [AuthController::class, 'processInscription'])->name('auth.process.inscription');
        Route::post('/connection', [AuthController::class, 'processConnection'])->name('auth.process.connection');
    });
    Route::get('/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
});


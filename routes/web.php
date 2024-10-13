<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlatsController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\SemaineController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'showHome'])->name('home');

Route::prefix('/debug')->group(function () {
    Route::get('/', [HomeController::class, 'showDebug'])->name('debug');
    Route::get('/reset', [HomeController::class, 'resetDebug'])->name('debug.reset');
});

Route::get('/plats', [PlatsController::class, 'showPlats'])->name('plats');
Route::get('/ingredients', [PlatsController::class, 'showIngredients'])->name('ingredients');
Route::get('/refresh', [SemaineController::class, 'prepareAWeek'])->name('refresh');

Route::prefix('/preferences')->group(function () {
    Route::get('/', [PreferenceController::class, 'showPreferences'])->name('preferences');
    Route::post('/update', [PreferenceController::class, 'updatePreferences'])->name('preferences.update');
});

Route::prefix('/add')->group(function () {
    Route::post('/dish', [PlatsController::class, 'addDish'])->name('add.dish');
    Route::post('/ingredient', [PlatsController::class, 'addIngredient'])->name('add.ingredient');
    Route::post('/groceries_purchase', [PlatsController::class, 'addGroceriesPurchase'])->name('add.groceries');
});

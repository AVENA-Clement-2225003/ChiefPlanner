<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlatsController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\SemaineController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'showHome'])->name('home');
Route::get('/plats', [PlatsController::class, 'showPlats'])->name('plats');
Route::get('/ingredients', [PlatsController::class, 'showIngredients'])->name('ingredients');
Route::get('/refresh', [SemaineController::class, 'prepareAWeek'])->name('refresh');
Route::prefix('/preferences')->group(function () {
    Route::get('/', [PreferenceController::class, 'showPreferences']);
    Route::post('/update', [PreferenceController::class, 'updatePreferences']);
});

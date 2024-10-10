<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlatsController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'showHome'])->name('home');
Route::get('/plats', [PlatsController::class, 'showPlats'])->name('plats');
Route::get('/ingredients', [PlatsController::class, 'showIngredients'])->name('ingredients');
Route::get('/preferences', [PreferenceController::class, 'showPreferences'])->name('ingredients');

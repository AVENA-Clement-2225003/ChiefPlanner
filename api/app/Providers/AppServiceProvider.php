<?php

namespace App\Providers;

use App\Planning\RandomSelection;
use App\Planning\SelectionStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SelectionStrategy::class, RandomSelection::class);
    }

    public function boot(): void {}
}

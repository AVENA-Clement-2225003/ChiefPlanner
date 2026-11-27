<?php

namespace App\Providers;

use App\Planning\LeftoverAwareSelection;
use App\Planning\SelectionStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SelectionStrategy::class, LeftoverAwareSelection::class);
    }

    public function boot(): void {}
}

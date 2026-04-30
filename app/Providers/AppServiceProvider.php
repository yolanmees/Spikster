<?php

namespace App\Providers;

use App\Services\DaemonService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DaemonService::class, fn () => new DaemonService());
    }

    public function boot(): void
    {
        // Fix livewire script route
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/vendor/livewire/livewire.js', $handle);
        });
    }
}

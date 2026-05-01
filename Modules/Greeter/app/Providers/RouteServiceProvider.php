<?php

namespace Modules\Greeter\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'auth', 'verified'])
            ->prefix('greeter')
            ->name('greeter.')
            ->group(__DIR__.'/../../routes/web.php');
    }
}

<?php

namespace App\Providers;

use App\Services\DaemonService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DaemonService::class, fn () => new DaemonService);
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/vendor/livewire/livewire.js', $handle);
        });

        $this->pushLoggingProcessor();
    }

    protected function pushLoggingProcessor(): void
    {
        try {
            $logger = Log::driver()->getLogger();
            $logger->pushProcessor(function ($record) {
                try {
                    $request = app(Request::class);
                    $requestId = $request->attributes->get('request_id');
                    if ($requestId) {
                        $record['extra']['request_id'] = $requestId;
                    }
                } catch (\Throwable) {
                }

                return $record;
            });
        } catch (\Throwable) {
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnforceProductionSafety
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && config('app.debug')) {
            Log::critical('APP_DEBUG is enabled in production — this exposes stack traces and sensitive data. Set APP_DEBUG=false immediately.');

            // Strip any debug data from outgoing responses by ensuring debug is off at runtime
            config(['app.debug' => false]);
        }

        return $next($request);
    }
}

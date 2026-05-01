<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Enforce HTTPS
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy (adjust as needed)
        $reverbHost = config('reverb.servers.reverb.host', 'localhost');
        $reverbPort = config('reverb.servers.reverb.port', 8080);
        $wsHosts = app()->environment('local')
            ? "ws://localhost:* wss://localhost:* ws://127.0.0.1:* wss://127.0.0.1:* ws://{$reverbHost}:{$reverbPort} wss://{$reverbHost}:{$reverbPort}"
            : "ws://{$reverbHost}:{$reverbPort} wss://{$reverbHost}:{$reverbPort}";

        $vitePort = env('VITE_PORT', 5173);
        $viteSrc = app()->environment('local')
            ? "http://localhost:{$vitePort} http://127.0.0.1:{$vitePort}"
            : '';

        $csp = "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://cdn.datatables.net {$viteSrc}; "
            ."style-src 'self' 'unsafe-inline' https://cdn.datatables.net https://fonts.cdnfonts.com https://cdnjs.cloudflare.com {$viteSrc}; "
            ."img-src 'self' data: https:; "
            ."font-src 'self' data: https://fonts.cdnfonts.com https://cdnjs.cloudflare.com {$viteSrc}; "
            ."connect-src 'self' https://cdn.jsdelivr.net {$wsHosts} {$viteSrc}; "
            ."frame-ancestors 'self'";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}

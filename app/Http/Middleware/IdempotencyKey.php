<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IdempotencyKey
{
    // TTL in seconds — long enough to cover async provisioning
    private const TTL = 86400;

    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return $next($request);
        }

        if (strlen($key) > 255) {
            return response()->json([
                'message' => 'Idempotency-Key must not exceed 255 characters.',
                'errors' => 'invalid_idempotency_key',
            ], 422);
        }

        $cacheKey = 'idempotency:'.sha1($request->user()?->id.'|'.$key);

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return response()->json(
                $cached['body'],
                $cached['status']
            )->header('Idempotent-Replayed', 'true');
        }

        $response = $next($request);

        // Only cache successful mutations (2xx)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body'   => json_decode($response->getContent(), true),
            ], self::TTL);
        }

        return $response;
    }
}

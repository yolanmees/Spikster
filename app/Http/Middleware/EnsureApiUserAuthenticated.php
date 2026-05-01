<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiUserAuthenticated
{
    /**
     * Prefer User/Sanctum auth for API requests.
     * Optionally falls back to legacy CipiAuth while dual-stack migration is enabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('security.api.prefer_user_sanctum_auth', true) && $this->authenticateWithSanctumUser($request)) {
            return $next($request);
        }

        if (config('security.api.allow_legacy_cipi_auth', true)) {
            $request->attributes->set('auth_source', 'legacy_cipi');

            return app(CipiAuth::class)->handle($request, $next);
        }

        return response()->json([
            'message' => 'Unauthenticated.',
            'errors' => 'Missing or invalid API authentication.',
        ], 401);
    }

    protected function authenticateWithSanctumUser(Request $request): bool
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            return false;
        }

        // Ensure request()->user() resolves to the authenticated User for policies/audit.
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('auth_source', 'sanctum_user');

        return true;
    }
}

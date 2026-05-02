<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiUserAuthenticated
{
    /**
     * Authenticate API requests via Sanctum (personal access tokens or SPA cookies).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->authenticateWithSanctumUser($request)) {
            return $next($request);
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

        // Log API key usage for auditing (skip read-only GET/HEAD to reduce noise)
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            AuditService::log(
                eventType: 'api_request',
                description: $request->method().' '.$request->path(),
                severity: 'info',
                newValues: [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'token_name' => $request->user()?->currentAccessToken()?->name ?? 'unknown',
                ],
            );
        }

        return true;
    }
}

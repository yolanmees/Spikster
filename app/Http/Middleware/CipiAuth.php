<?php

namespace App\Http\Middleware;

use App\Models\Auth;
use App\Models\User;
use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Illuminate\Support\Str;

/**
 * @deprecated Use Sanctum token authentication via EnsureApiUserAuthenticated instead.
 *             Kept for backward compatibility with legacy Cipi API clients.
 *             Will be removed in a future release.
 */
class CipiAuth
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->path() == 'api/login') {
            return $next($request);
        }

        $auth = $request->header('Authorization');

        // Try Sanctum first if the bearer token is a valid Sanctum token
        if ($auth && Str::startsWith($auth, 'Bearer ')
            && $user = AuthFacade::guard('sanctum')->user()) {
            $this->setAuthenticatedUser($request, $user);

            return $next($request);
        }

        $token = null;
        $apikey = null;

        if (Str::startsWith($auth, 'Bearer ')) {
            $token = Str::substr($auth, 7);
        }

        if (Str::startsWith($auth, 'Apikey ')) {
            $apikey = Str::substr($auth, 7);
        }

        if (! $token && ! $apikey) {
            return response()->json([
                'message' => 'Authorization header missed in payload.',
                'errors' => 'Missing Authorization.',
            ], 422);
        }

        if ($token) {
            try {
                JWT::decode($token, new Key(config('cipi.jwt_secret'), 'HS256'));
                $this->resolveLegacyUser($request);
            } catch (ExpiredException $e) {
                return response()->json([
                    'message' => 'Given token is expired.',
                    'errors' => 'Expired token.',
                ], 401);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Given token is invalid.',
                    'errors' => 'Invalid token.',
                ], 401);
            }
        }

        if ($apikey) {
            if (! Auth::where('apikey', $apikey)->first()) {
                return response()->json([
                    'message' => 'Given API Key is invalid.',
                    'errors' => 'Invalid API Key.',
                ], 401);
            }

            $this->resolveLegacyUser($request);
        }

        $request->attributes->set('auth_source', 'legacy_cipi');

        return $next($request);
    }

    /**
     * Set the authenticated user on the request and auth guard.
     */
    private function setAuthenticatedUser(Request $request, \Illuminate\Contracts\Auth\Authenticatable $user): void
    {
        AuthFacade::setUser($user);
        $request->setUserResolver(fn () => $user);
    }

    /**
     * Resolve a User model for legacy Cipi JWT/API-key auth.
     *
     * Prioritises the panel admin email (CIPI_USERNAME), then the first Super Admin,
     * then the first User in the database.
     */
    private function resolveLegacyUser(Request $request): void
    {
        if ($request->user()) {
            return;
        }

        $user = User::where('email', config('cipi.username'))->first()
            ?? (method_exists(User::class, 'role')
                ? User::role('Super Admin')->first()
                : null)
            ?? User::first();

        if ($user) {
            $this->setAuthenticatedUser($request, $user);
        }
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\TwoFactorAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Require2FA
{
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if user has 2FA enabled
        if (!$this->twoFactorService->has2FAEnabled($user)) {
            return $next($request);
        }

        // Check if 2FA is already verified for this session
        if ($request->session()->get('2fa_verified', false)) {
            return $next($request);
        }

        // Check if device is trusted
        $deviceToken = $request->cookie('2fa_device_token');
        if ($deviceToken && $this->twoFactorService->isDeviceTrusted($user, $deviceToken)) {
            // Mark session as verified
            $request->session()->put('2fa_verified', true);
            return $next($request);
        }

        // Redirect to 2FA verification page
        if ($request->expectsJson()) {
            return response()->json([
                'message' => '2FA verification required',
                'requires_2fa' => true,
            ], 403);
        }

        return redirect()->route('2fa.verify');
    }
}

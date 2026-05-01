<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use App\Services\AuditService;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Fortify\Rules\Password;

class AuthController extends Controller
{
    /**
     * Auth login via username and password for mobile app
     */
    public function appLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $lockoutKey = 'login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($lockoutKey, 5)) {
            $seconds = RateLimiter::availableIn($lockoutKey);

            return response()->json([
                'message' => __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
                'errors' => 'too_many_attempts',
            ], 429);
        }

        $user = Auth::attempt($request->username, $request->password);

        if (! $user) {
            RateLimiter::hit($lockoutKey, 300);

            return response()->json([
                'message' => __('spikster.invalid_login_message'),
                'errors' => __('spikster.invalid_login'),
            ], 401);
        }

        RateLimiter::clear($lockoutKey);

        return response()->json([
            'username' => $user->username,
            'apikey' => $user->apikey,
        ]);
    }

    /**
     * JWT Auth login via username and password
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $lockoutKey = 'login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($lockoutKey, 5)) {
            $seconds = RateLimiter::availableIn($lockoutKey);

            return response()->json([
                'message' => __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
                'errors' => 'too_many_attempts',
            ], 429);
        }

        $user = Auth::attempt($request->username, $request->password);

        if (! $user) {
            RateLimiter::hit($lockoutKey, 300);
            AuditService::logFailedLogin($request->username);

            return response()->json([
                'message' => __('spikster.invalid_login_message'),
                'errors' => __('spikster.invalid_login'),
            ], 401);
        }

        RateLimiter::clear($lockoutKey);

        $user->jwt = JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_refresh')], config('cipi.jwt_secret').'-Rfs', 'HS256');
        $user->save();

        // Log successful login
        AuditService::logLogin($user->id);

        return response()->json([
            'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs', 'HS256'),
            'refresh_token' => $user->jwt,
            'username' => $user->username,
        ]);
    }

    /**
     * JWT Auth refresh token
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'refresh_token' => 'required',
        ]);

        $lockoutKey = 'refresh:'.$request->ip();

        if (RateLimiter::tooManyAttempts($lockoutKey, 10)) {
            $seconds = RateLimiter::availableIn($lockoutKey);

            return response()->json([
                'message' => __('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
                'errors' => 'too_many_attempts',
            ], 429);
        }

        $user = Auth::check($request->username, $request->refresh_token);

        if ($user) {
            RateLimiter::clear($lockoutKey);
            $user->jwt = JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_refresh')], config('cipi.jwt_secret').'-Rfs', 'HS256');
            $user->save();

            return response()->json([
                'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs', 'HS256'),
                'refresh_token' => $user->jwt,
                'username' => $user->username,
            ]);
        } else {
            RateLimiter::hit($lockoutKey, 300);

            return response()->json([
                'message' => __('spikster.invalid_token_message'),
                'errors' => __('spikster.invalid_token'),
            ], 401);
        }
    }

    /**
     * Auth profile patch
     */
    public function update(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Auth::attempt($request->username, $request->password);

        if (! $user) {
            return response()->json([
                'message' => __('spikster.invalid_login_message'),
                'errors' => __('spikster.invalid_login'),
            ], 401);
        }

        if ($request->newusername) {
            $request->validate([
                'newusername' => 'required|min:6|max:64',
            ]);

            $newuser = Str::lower($request->newusername);

            if (! Auth::where('username', $newuser)->first()) {
                $user->username = $newuser;
            } else {
                return response()->json([
                    'message' => __('spikster.username_conflict_message'),
                    'errors' => __('spikster.username_conflict'),
                ], 409);
            }
        }

        if ($request->newpassword) {
            if (Hash::check($request->newpassword, $user->password)) {
                return response()->json([
                    'message' => 'New password must differ from the current password.',
                    'errors' => 'password_reuse',
                ], 422);
            }

            $request->validate([
                'newpassword' => [
                    'required',
                    'max:64',
                    (new Password)
                        ->length(12)
                        ->requireUppercase()
                        ->requireNumeric()
                        ->requireSpecialCharacter(),
                ],
            ]);
            $user->password = Hash::make($request->newpassword);
        }

        if ($request->apikey) {
            $user->apikey = Str::random(48);
        }

        $user->save();

        return response()->json([
            'access_token' => JWT::encode(['iat' => time(), 'exp' => time() + config('cipi.jwt_access')], config('cipi.jwt_secret').'-Acs', 'HS256'),
            'refresh_token' => $user->jwt,
            'username' => $user->username,
            'apikey' => $user->apikey,
        ]);
    }

    /**
     * JWT Auth sign out
     */
    public function logout(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'refresh_token' => 'required',
        ]);

        $user = Auth::check($request->username, $request->refresh_token);

        if ($user) {
            $user->jwt = null;
            $user->save();
        } else {
            return response()->json([
                'message' => __('spikster.invalid_token_message'),
                'errors' => __('spikster.invalid_token'),
            ], 401);
        }
    }
}

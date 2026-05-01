<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Get 2FA status and statistics for the authenticated user.
     */
    public function status(Request $request)
    {
        try {
            $stats = $this->twoFactorService->get2FAStats($request->user());

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve 2FA status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a new secret for 2FA setup.
     */
    public function generateSecret(Request $request)
    {
        try {
            $user = $request->user();
            $secret = $this->twoFactorService->generateSecret();
            $qrCode = $this->twoFactorService->getQRCode($user, $secret);

            return response()->json([
                'success' => true,
                'data' => [
                    'secret' => $secret,
                    'qr_code' => $qrCode,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate secret',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enable 2FA for the authenticated user.
     */
    public function enable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
            'recovery_email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // Create a temporary Google2FA instance to verify the code
            $google2fa = new Google2FA;
            $valid = $google2fa->verifyKey($request->secret, $request->code);

            if (! $valid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code',
                ], 422);
            }

            $settings = $this->twoFactorService->enable2FA(
                $user,
                $request->secret,
                $request->recovery_email
            );

            // Generate backup codes
            $backupCodes = $this->twoFactorService->generateBackupCodes($user);

            return response()->json([
                'success' => true,
                'message' => '2FA has been enabled successfully',
                'data' => [
                    'backup_codes' => $backupCodes,
                    'settings' => $settings,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enable 2FA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disable 2FA for the authenticated user.
     */
    public function disable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // Verify the code before disabling
            if (! $this->twoFactorService->verify2FA($user, $request->code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code',
                ], 422);
            }

            $this->twoFactorService->disable2FA($user);

            return response()->json([
                'success' => true,
                'message' => '2FA has been disabled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable 2FA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify a 2FA code during login.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'trust_device' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            if (! $this->twoFactorService->verify2FA($user, $request->code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code',
                ], 422);
            }

            // Mark session as verified
            $request->session()->put('2fa_verified', true);

            // Trust device if requested
            $deviceToken = null;
            if ($request->trust_device) {
                $device = $this->twoFactorService->trustDevice($user, $request);
                $deviceToken = $device->device_token;
            }

            return response()->json([
                'success' => true,
                'message' => '2FA verification successful',
                'data' => [
                    'device_token' => $deviceToken,
                ],
            ])->cookie('2fa_device_token', $deviceToken, 43200); // 30 days
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate backup codes.
     */
    public function regenerateBackupCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // Verify code before regenerating
            if (! $this->twoFactorService->verify2FA($user, $request->code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code',
                ], 422);
            }

            $backupCodes = $this->twoFactorService->generateBackupCodes($user);

            return response()->json([
                'success' => true,
                'message' => 'Backup codes regenerated successfully',
                'data' => [
                    'backup_codes' => $backupCodes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate backup codes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all trusted devices.
     */
    public function getTrustedDevices(Request $request)
    {
        try {
            $devices = $request->user()
                ->trustedDevices()
                ->active()
                ->orderBy('last_used_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $devices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trusted devices',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a trusted device.
     */
    public function removeTrustedDevice(Request $request, string $deviceId)
    {
        try {
            $this->twoFactorService->removeTrustedDevice($request->user(), $deviceId);

            return response()->json([
                'success' => true,
                'message' => 'Trusted device removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove trusted device',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get audit logs.
     */
    public function getAuditLogs(Request $request)
    {
        try {
            $logs = $request->user()
                ->twoFactorAuditLogs()
                ->recent(30)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

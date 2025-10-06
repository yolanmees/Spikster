<?php

namespace App\Services;

use App\Models\BackupCode;
use App\Models\TrustedDevice;
use App\Models\TwoFactorAuditLog;
use App\Models\User;
use App\Models\User2FASetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for TOTP.
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Enable 2FA for a user.
     */
    public function enable2FA(User $user, string $secret, ?string $recoveryEmail = null): User2FASetting
    {
        return DB::transaction(function () use ($user, $secret, $recoveryEmail) {
            // Create or update 2FA settings
            $settings = User2FASetting::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_enabled' => true,
                    'secret_key' => $secret,
                    'enabled_at' => now(),
                    'recovery_email' => $recoveryEmail,
                    'failed_attempts' => 0,
                    'locked_until' => null,
                ]
            );

            // Generate backup codes
            $this->generateBackupCodes($user);

            // Log the event
            TwoFactorAuditLog::logEvent(
                $user->id,
                TwoFactorAuditLog::ACTION_ENABLED,
                request()->ip(),
                request()->userAgent(),
                ['recovery_email' => $recoveryEmail]
            );

            return $settings;
        });
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable2FA(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Disable 2FA
            $settings = $user->twoFactorSettings;
            if ($settings) {
                $settings->update([
                    'is_enabled' => false,
                    'secret_key' => null,
                ]);
            }

            // Delete backup codes
            $user->backupCodes()->delete();

            // Delete trusted devices
            $user->trustedDevices()->delete();

            // Log the event
            TwoFactorAuditLog::logEvent(
                $user->id,
                TwoFactorAuditLog::ACTION_DISABLED,
                request()->ip(),
                request()->userAgent()
            );
        });
    }

    /**
     * Verify a TOTP code.
     */
    public function verifyTOTP(User $user, string $code): bool
    {
        $settings = $user->twoFactorSettings;

        if (!$settings || !$settings->is_enabled) {
            return false;
        }

        // Check if account is locked
        if ($settings->isLocked()) {
            return false;
        }

        $secret = $settings->decrypted_secret_key;
        if (!$secret) {
            return false;
        }

        $valid = $this->google2fa->verifyKey($secret, $code, 1); // Allow 1 window of time drift

        if ($valid) {
            $settings->resetFailedAttempts();

            TwoFactorAuditLog::logEvent(
                $user->id,
                TwoFactorAuditLog::ACTION_VERIFIED,
                request()->ip(),
                request()->userAgent(),
                ['method' => 'totp']
            );
        } else {
            $settings->incrementFailedAttempts();

            TwoFactorAuditLog::logEvent(
                $user->id,
                TwoFactorAuditLog::ACTION_FAILED,
                request()->ip(),
                request()->userAgent(),
                [
                    'method' => 'totp',
                    'failed_attempts' => $settings->failed_attempts,
                ]
            );
        }

        return $valid;
    }

    /**
     * Verify a backup code.
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        $settings = $user->twoFactorSettings;

        if (!$settings || !$settings->is_enabled) {
            return false;
        }

        // Check if account is locked
        if ($settings->isLocked()) {
            return false;
        }

        // Find an unused backup code that matches
        $backupCode = $user->backupCodes()
            ->unused()
            ->get()
            ->first(function ($bc) use ($code) {
                return $bc->verifyCode($code);
            });

        if ($backupCode) {
            $backupCode->markAsUsed(request()->ip());
            $settings->resetFailedAttempts();

            TwoFactorAuditLog::logEvent(
                $user->id,
                TwoFactorAuditLog::ACTION_BACKUP_CODE_USED,
                request()->ip(),
                request()->userAgent(),
                ['backup_code_id' => $backupCode->id]
            );

            return true;
        }

        $settings->incrementFailedAttempts();

        TwoFactorAuditLog::logEvent(
            $user->id,
            TwoFactorAuditLog::ACTION_FAILED,
            request()->ip(),
            request()->userAgent(),
            [
                'method' => 'backup_code',
                'failed_attempts' => $settings->failed_attempts,
            ]
        );

        return false;
    }

    /**
     * Verify 2FA using TOTP or backup code.
     */
    public function verify2FA(User $user, string $code): bool
    {
        // Try TOTP first (6 digits)
        if (strlen($code) === 6 && ctype_digit($code)) {
            return $this->verifyTOTP($user, $code);
        }

        // Try backup code (format: ABCD-1234)
        if (preg_match('/^[A-Z0-9]{4}-[0-9]{4}$/i', $code)) {
            return $this->verifyBackupCode($user, strtoupper($code));
        }

        return false;
    }

    /**
     * Generate backup codes for a user.
     */
    public function generateBackupCodes(User $user, int $count = 10): array
    {
        // Delete existing backup codes
        $user->backupCodes()->delete();

        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = BackupCode::generateCode();
            $codes[] = $code;

            BackupCode::create([
                'user_id' => $user->id,
                'code' => $code,
                'code_hash' => BackupCode::hashCode($code),
            ]);
        }

        return $codes;
    }

    /**
     * Get QR code for TOTP setup.
     */
    public function getQRCode(User $user, string $secret): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Trust a device for 30 days.
     */
    public function trustDevice(User $user, Request $request): TrustedDevice
    {
        $deviceInfo = TrustedDevice::parseFromRequest($request);

        $device = TrustedDevice::create([
            'user_id' => $user->id,
            'device_token' => TrustedDevice::generateToken(),
            'last_used_at' => now(),
            'expires_at' => now()->addDays(30),
            ...$deviceInfo,
        ]);

        TwoFactorAuditLog::logEvent(
            $user->id,
            TwoFactorAuditLog::ACTION_DEVICE_TRUSTED,
            $request->ip(),
            $request->userAgent(),
            ['device_id' => $device->id]
        );

        return $device;
    }

    /**
     * Check if current device is trusted.
     */
    public function isDeviceTrusted(User $user, string $deviceToken): bool
    {
        $device = $user->trustedDevices()
            ->where('device_token', $deviceToken)
            ->active()
            ->first();

        if ($device) {
            // Update last used timestamp
            $device->touch();
            return true;
        }

        return false;
    }

    /**
     * Remove a trusted device.
     */
    public function removeTrustedDevice(User $user, string $deviceId): void
    {
        $device = $user->trustedDevices()->find($deviceId);

        if ($device) {
            TwoFactorAuditLog::logEvent(
                $user->id,
                TwoFactorAuditLog::ACTION_DEVICE_REMOVED,
                request()->ip(),
                request()->userAgent(),
                ['device_id' => $deviceId]
            );

            $device->delete();
        }
    }

    /**
     * Clean up expired trusted devices.
     */
    public function cleanupExpiredDevices(User $user): int
    {
        return $user->trustedDevices()->expired()->delete();
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function has2FAEnabled(User $user): bool
    {
        $settings = $user->twoFactorSettings;
        return $settings && $settings->is_enabled;
    }

    /**
     * Get 2FA statistics for a user.
     */
    public function get2FAStats(User $user): array
    {
        return [
            'is_enabled' => $this->has2FAEnabled($user),
            'enabled_at' => $user->twoFactorSettings?->enabled_at,
            'last_verified_at' => $user->twoFactorSettings?->last_verified_at,
            'failed_attempts' => $user->twoFactorSettings?->failed_attempts ?? 0,
            'is_locked' => $user->twoFactorSettings?->isLocked() ?? false,
            'locked_until' => $user->twoFactorSettings?->locked_until,
            'backup_codes_remaining' => $user->backupCodes()->unused()->count(),
            'trusted_devices_count' => $user->trustedDevices()->active()->count(),
            'recent_activity' => $user->twoFactorAuditLogs()
                ->recent(30)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrustedDevice extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'device_token',
        'device_name',
        'device_type',
        'browser',
        'platform',
        'ip_address',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the trusted device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new device token.
     */
    public static function generateToken(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Check if the device is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Update the last used timestamp and extend expiration.
     */
    public function touch(): void
    {
        $this->update([
            'last_used_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    /**
     * Scope to only active (non-expired) devices.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to only expired devices.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Parse device information from request.
     */
    public static function parseFromRequest($request): array
    {
        $userAgent = $request->userAgent();

        // Basic parsing - can be enhanced with a library if needed
        $deviceType = 'Desktop';
        if (preg_match('/mobile/i', $userAgent)) {
            $deviceType = 'Mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            $deviceType = 'Tablet';
        }

        // Extract browser
        $browser = 'Unknown';
        if (preg_match('/chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        // Extract platform
        $platform = 'Unknown';
        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/mac/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/ios|iphone|ipad/i', $userAgent)) {
            $platform = 'iOS';
        }

        return [
            'device_name' => "$browser op $platform",
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
            'ip_address' => $request->ip(),
        ];
    }
}

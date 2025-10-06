<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorAuditLog extends Model
{
    use HasUuids;

    // Action types
    const ACTION_ENABLED = 'enabled';
    const ACTION_DISABLED = 'disabled';
    const ACTION_VERIFIED = 'verified';
    const ACTION_FAILED = 'failed';
    const ACTION_BACKUP_CODE_USED = 'backup_code_used';
    const ACTION_DEVICE_TRUSTED = 'device_trusted';
    const ACTION_DEVICE_REMOVED = 'device_removed';

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the audit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a 2FA event.
     */
    public static function logEvent(
        int $userId,
        string $action,
        string $ipAddress,
        ?string $userAgent = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope to specific action types.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to recent logs.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to failed attempts.
     */
    public function scopeFailedAttempts($query)
    {
        return $query->where('action', self::ACTION_FAILED);
    }

    /**
     * Scope to successful verifications.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('action', [self::ACTION_VERIFIED, self::ACTION_BACKUP_CODE_USED]);
    }
}

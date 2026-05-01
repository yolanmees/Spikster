<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionAuditLog extends Model
{
    use HasUuids;

    // Action constants
    const ACTION_ROLE_ASSIGNED = 'role_assigned';

    const ACTION_ROLE_REVOKED = 'role_revoked';

    const ACTION_PERMISSION_GRANTED = 'permission_granted';

    const ACTION_PERMISSION_REVOKED = 'permission_revoked';

    const ACTION_SITE_ACCESS_GRANTED = 'site_access_granted';

    const ACTION_SITE_ACCESS_REVOKED = 'site_access_revoked';

    const UPDATED_AT = null; // Only created_at

    protected $fillable = [
        'user_id',
        'action',
        'target_user_id',
        'role_id',
        'permission_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the target user (who was affected).
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Get the role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the permission.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Log a permission event.
     */
    public static function logEvent(
        int $userId,
        string $action,
        ?int $targetUserId = null,
        ?int $roleId = null,
        ?int $permissionId = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'target_user_id' => $targetUserId,
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope to specific action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to recent logs.
     */
    public function scopeRecent($query, int $days = 90)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to specific user (performer).
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to specific target user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('target_user_id', $userId);
    }
}

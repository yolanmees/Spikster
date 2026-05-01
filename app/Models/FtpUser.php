<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class FtpUser extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'site_id',
        'server_id',
        'username',
        'password',
        'home_directory',
        'quota_mb',
        'max_connections',
        'bandwidth_limit_kbps',
        'permissions',
        'is_active',
        'require_ssl',
        'allowed_ip',
        'current_usage_bytes',
        'last_login_at',
        'last_login_ip',
        'failed_login_count',
        'locked_until',
        'notes',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'require_ssl' => 'boolean',
        'quota_mb' => 'integer',
        'max_connections' => 'integer',
        'bandwidth_limit_kbps' => 'integer',
        'current_usage_bytes' => 'integer',
        'failed_login_count' => 'integer',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    protected $attributes = [
        'permissions' => '{"read":true,"write":true,"delete":true,"rename":true,"create_directory":true}',
        'is_active' => true,
        'require_ssl' => true,
        'quota_mb' => 1024,
        'max_connections' => 5,
        'current_usage_bytes' => 0,
        'failed_login_count' => 0,
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Relationships
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('locked_until')
                    ->orWhere('locked_until', '<', now());
            });
    }

    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeLocked($query)
    {
        return $query->whereNotNull('locked_until')
            ->where('locked_until', '>', now());
    }

    /**
     * Password Management
     */
    public function setPasswordAttribute($value)
    {
        // Only hash if it's not already hashed
        if (! str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Quota Management
     */
    public function getQuotaUsagePercentage(): float
    {
        if ($this->quota_mb === 0) {
            return 0;
        }

        $quotaBytes = $this->quota_mb * 1024 * 1024;

        return min(100, round(($this->current_usage_bytes / $quotaBytes) * 100, 2));
    }

    public function getRemainingQuotaBytes(): int
    {
        $quotaBytes = $this->quota_mb * 1024 * 1024;

        return max(0, $quotaBytes - $this->current_usage_bytes);
    }

    public function getRemainingQuotaMB(): float
    {
        return round($this->getRemainingQuotaBytes() / (1024 * 1024), 2);
    }

    public function isQuotaExceeded(): bool
    {
        return $this->current_usage_bytes >= ($this->quota_mb * 1024 * 1024);
    }

    public function isNearQuotaLimit(): bool
    {
        return $this->getQuotaUsagePercentage() >= 80;
    }

    /**
     * Security
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_count');

        // Lock account after 5 failed attempts for 30 minutes
        if ($this->failed_login_count >= 5) {
            $this->update([
                'locked_until' => now()->addMinutes(30),
            ]);
        }
    }

    public function resetFailedLogins(): void
    {
        $this->update([
            'failed_login_count' => 0,
            'locked_until' => null,
        ]);
    }

    public function recordSuccessfulLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'failed_login_count' => 0,
            'locked_until' => null,
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'locked_until' => null,
            'failed_login_count' => 0,
        ]);
    }

    /**
     * Permissions
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions[$permission] ?? false;
    }

    public function canRead(): bool
    {
        return $this->hasPermission('read') && $this->is_active && ! $this->isLocked();
    }

    public function canWrite(): bool
    {
        return $this->hasPermission('write') && $this->is_active && ! $this->isLocked();
    }

    public function canDelete(): bool
    {
        return $this->hasPermission('delete') && $this->is_active && ! $this->isLocked();
    }

    public function canRename(): bool
    {
        return $this->hasPermission('rename') && $this->is_active && ! $this->isLocked();
    }

    public function canCreateDirectory(): bool
    {
        return $this->hasPermission('create_directory') && $this->is_active && ! $this->isLocked();
    }

    public function setPermissions(array $permissions): void
    {
        $this->update(['permissions' => array_merge($this->permissions, $permissions)]);
    }

    /**
     * Helpers - Formatting
     */
    public function getFormattedQuota(): string
    {
        if ($this->quota_mb >= 1024) {
            return round($this->quota_mb / 1024, 2).' GB';
        }

        return $this->quota_mb.' MB';
    }

    public function getFormattedUsage(): string
    {
        $mb = round($this->current_usage_bytes / (1024 * 1024), 2);
        if ($mb >= 1024) {
            return round($mb / 1024, 2).' GB';
        }

        return $mb.' MB';
    }

    public function getFormattedBandwidth(): ?string
    {
        if (! $this->bandwidth_limit_kbps) {
            return 'Unlimited';
        }

        if ($this->bandwidth_limit_kbps >= 1024) {
            return round($this->bandwidth_limit_kbps / 1024, 2).' MB/s';
        }

        return $this->bandwidth_limit_kbps.' KB/s';
    }

    /**
     * Helpers - Status
     */
    public function getStatusBadgeColor(): string
    {
        if (! $this->is_active) {
            return 'gray';
        }
        if ($this->isLocked()) {
            return 'red';
        }
        if ($this->isQuotaExceeded()) {
            return 'orange';
        }
        if ($this->isNearQuotaLimit()) {
            return 'yellow';
        }

        return 'green';
    }

    public function getStatusText(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }
        if ($this->isLocked()) {
            return 'Locked';
        }
        if ($this->isQuotaExceeded()) {
            return 'Quota Exceeded';
        }
        if ($this->isNearQuotaLimit()) {
            return 'Near Quota Limit';
        }

        return 'Active';
    }

    public function getQuotaBadgeColor(): string
    {
        $percentage = $this->getQuotaUsagePercentage();

        if ($percentage >= 100) {
            return 'red';
        }
        if ($percentage >= 80) {
            return 'orange';
        }
        if ($percentage >= 60) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Helpers - Connection Info
     */
    public function getConnectionInfo(): array
    {
        return [
            'host' => $this->server->ip,
            'username' => $this->username,
            'port_ftp' => 21,
            'port_ftps_explicit' => 21,
            'port_ftps_implicit' => 990,
            'protocol' => $this->require_ssl ? 'FTPS (SSL/TLS)' : 'FTP',
            'encryption' => $this->require_ssl ? 'Explicit TLS' : 'None',
            'passive_mode' => true,
            'home_directory' => $this->home_directory,
        ];
    }

    /**
     * Helpers - Statistics
     */
    public function getStatistics(): array
    {
        return [
            'current_usage_bytes' => $this->current_usage_bytes,
            'current_usage_formatted' => $this->getFormattedUsage(),
            'quota_bytes' => $this->quota_mb * 1024 * 1024,
            'quota_formatted' => $this->getFormattedQuota(),
            'usage_percentage' => $this->getQuotaUsagePercentage(),
            'remaining_bytes' => $this->getRemainingQuotaBytes(),
            'remaining_formatted' => $this->getFormattedQuota(),
            'is_quota_exceeded' => $this->isQuotaExceeded(),
            'is_near_quota_limit' => $this->isNearQuotaLimit(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'last_login_ip' => $this->last_login_ip,
            'failed_login_count' => $this->failed_login_count,
            'is_locked' => $this->isLocked(),
            'locked_until' => $this->locked_until?->toIso8601String(),
            'status' => $this->getStatusText(),
            'status_color' => $this->getStatusBadgeColor(),
        ];
    }

    /**
     * Update current disk usage
     */
    public function updateDiskUsage(int $bytes): void
    {
        $this->update(['current_usage_bytes' => $bytes]);
    }

    /**
     * Check if IP is allowed
     */
    public function isIpAllowed(string $ip): bool
    {
        // If no IP restriction, allow all
        if (! $this->allowed_ip) {
            return true;
        }

        // Check if IP matches (exact match or CIDR notation)
        if ($this->allowed_ip === $ip) {
            return true;
        }

        // TODO: Add CIDR range checking
        return false;
    }

    /**
     * Get human-readable time since last login
     */
    public function getLastLoginHuman(): ?string
    {
        if (! $this->last_login_at) {
            return null;
        }

        return $this->last_login_at->diffForHumans();
    }
}

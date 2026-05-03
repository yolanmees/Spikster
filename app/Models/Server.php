<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Server Model
 *
 * @property int $id
 * @property string $server_id
 * @property string $ip
 * @property string $name
 * @property string $password
 * @property string $database
 * @property string|null $provider
 * @property string|null $location
 * @property string $php
 * @property string|null $github_key
 * @property string|null $cron
 * @property bool $default
 * @property int|null $build
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Server extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'server_id',
        'ip',
        'name',
        'password',
        'database',
        'provider',
        'location',
        'php',
        'github_key',
        'cron',
        'default',
        'build',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'password',
        'database',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default' => 'boolean',
        'build' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Sensitive fields — encrypted at rest using APP_KEY
        'password' => 'encrypted',
        'database' => 'encrypted',
    ];

    /**
     * Get all sites for this server (excluding panel sites).
     *
     * @return HasMany<Site>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class)->where('panel', false);
    }

    /**
     * Get all sites for this server (including panel sites).
     *
     * @return HasMany<Site>
     */
    public function allsites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * Get all metrics for this server.
     *
     * @return HasMany<ServerMetric>
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ServerMetric::class, 'server_id', 'id');
    }

    /**
     * Get the latest metric for this server.
     */
    public function latestMetric(): ?ServerMetric
    {
        return $this->metrics()->latest('measured_at')->first();
    }

    /**
     * Get all cron jobs for this server.
     *
     * @return HasMany<CronJob>
     */
    public function cronJobs(): HasMany
    {
        return $this->hasMany(CronJob::class);
    }

    /**
     * Check if server is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    /**
     * Check if this is the default server.
     */
    public function isDefault(): bool
    {
        return $this->default === true;
    }

    /**
     * Get server display name with IP.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->ip})";
    }

    /**
     * Scope a query to only include active servers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include the default server.
     */
    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }

    /**
     * Scope a query to only include servers by provider.
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope a query to only include servers by build version.
     */
    public function scopeByBuild($query, int $build)
    {
        return $query->where('build', $build);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Site Model
 *
 * @property int $id
 * @property string $site_id
 * @property int $server_id
 * @property string $domain
 * @property string $username
 * @property string $password
 * @property string $database
 * @property string|null $basepath
 * @property string|null $repository
 * @property string|null $branch
 * @property string $php
 * @property string|null $supervisor
 * @property string|null $nginx
 * @property string|null $deploy
 * @property bool $panel
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Server $server
 * @property-read Collection<int, Alias> $aliases
 */
class Site extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_id',
        'server_id',
        'domain',
        'username',
        'password',
        'database',
        'basepath',
        'repository',
        'branch',
        'php',
        'supervisor',
        'nginx',
        'deploy',
        'panel',
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
        'panel' => 'boolean',
        'server_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the server that owns the site.
     *
     * @return BelongsTo<Server, Site>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get all aliases for this site.
     *
     * @return HasMany<Alias>
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(Alias::class);
    }

    /**
     * Get all domains for this site.
     *
     * @return HasMany<Domain>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'site_id', 'site_id');
    }

    /**
     * Get the primary domain for this site.
     */
    public function primaryDomain()
    {
        return $this->hasOne(Domain::class, 'site_id', 'site_id')->where('is_primary', true);
    }

    /**
     * Get all cron jobs for this site.
     *
     * @return HasMany<CronJob>
     */
    public function cronJobs(): HasMany
    {
        return $this->hasMany(CronJob::class);
    }

    /**
     * Get all email accounts for this site.
     *
     * @return HasMany<EmailAccount>
     */
    public function emailAccounts(): HasMany
    {
        return $this->hasMany(EmailAccount::class);
    }

    /**
     * Get all FTP users for this site.
     *
     * @return HasMany<FtpUser>
     */
    public function ftpUsers(): HasMany
    {
        return $this->hasMany(FtpUser::class);
    }

    /**
     * Check if this is a panel site.
     */
    public function isPanel(): bool
    {
        return $this->panel === true;
    }

    /**
     * Check if site has a Git repository configured.
     */
    public function hasRepository(): bool
    {
        return ! empty($this->repository);
    }

    /**
     * Get the root path for the site.
     */
    public function getRootPathAttribute(): string
    {
        return "/home/{$this->username}/{$this->domain}";
    }

    /**
     * Get the full public path for the site.
     */
    public function getPublicPathAttribute(): string
    {
        return $this->rootpath.($this->basepath ?? '/public');
    }

    /**
     * Scope a query to only include non-panel sites.
     */
    public function scopeNonPanel($query)
    {
        return $query->where('panel', false);
    }

    /**
     * Scope a query to only include sites by PHP version.
     */
    public function scopeByPhpVersion($query, string $version)
    {
        return $query->where('php', $version);
    }

    /**
     * Scope a query to only include sites with repositories.
     */
    public function scopeWithRepository($query)
    {
        return $query->whereNotNull('repository');
    }

    /**
     * Scope a query to only include sites belonging to a specific server.
     */
    public function scopeOnServer($query, int $serverId)
    {
        return $query->where('server_id', $serverId);
    }
}

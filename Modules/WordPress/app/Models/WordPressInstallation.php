<?php

namespace Modules\WordPress\Models;

use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordPressInstallation extends Model
{
    protected $table = 'wordpress_installations';

    protected $fillable = [
        'site_id',
        'path',
        'url',
        'version',
        'admin_username',
        'admin_password',
        'database_id',
        'database_user_id',
        'locale',
        'status',
        'auto_update',
        'is_multisite',
        'wp_config',
        'last_update_check',
        'installed_at',
    ];

    protected $casts = [
        'auto_update' => 'boolean',
        'is_multisite' => 'boolean',
        'wp_config' => 'array',
        'last_update_check' => 'datetime',
        'installed_at' => 'datetime',
    ];

    protected $hidden = [
        'admin_password',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    public function database(): BelongsTo
    {
        return $this->belongsTo(Database::class);
    }

    public function databaseUser(): BelongsTo
    {
        return $this->belongsTo(DatabaseUser::class);
    }

    public function themes(): HasMany
    {
        return $this->hasMany(WordPressTheme::class, 'wordpress_installation_id');
    }

    public function plugins(): HasMany
    {
        return $this->hasMany(WordPressPlugin::class, 'wordpress_installation_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(WordPressUpdate::class, 'wordpress_installation_id');
    }

    public function activeTheme(): HasMany
    {
        return $this->hasMany(WordPressTheme::class, 'wordpress_installation_id')->where('is_active', true);
    }

    public function activePlugins(): HasMany
    {
        return $this->hasMany(WordPressPlugin::class, 'wordpress_installation_id')->where('is_active', true);
    }

    public function pendingUpdates(): HasMany
    {
        return $this->hasMany(WordPressUpdate::class, 'wordpress_installation_id')->where('status', 'pending');
    }

    public function getAdminPasswordAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    public function setAdminPasswordAttribute($value)
    {
        $this->attributes['admin_password'] = $value ? encrypt($value) : null;
    }

    public function getFullPath(): string
    {
        return $this->site->rootpath.'/'.ltrim($this->path, '/');
    }

    public function getAdminUrl(): string
    {
        return rtrim($this->url ?? $this->site->domain, '/').'/wp-admin';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeStaging($query)
    {
        return $query->where('status', 'staging');
    }

    public function hasUpdatesAvailable(): bool
    {
        return $this->pendingUpdates()->exists() ||
               $this->themes()->whereNotNull('update_available')->exists() ||
               $this->plugins()->whereNotNull('update_available')->exists();
    }

    public function getUpdatesCount(): int
    {
        return $this->pendingUpdates()->count() +
               $this->themes()->whereNotNull('update_available')->count() +
               $this->plugins()->whereNotNull('update_available')->count();
    }
}

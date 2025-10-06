<?php

namespace Modules\WordPress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordPressPlugin extends Model
{
    protected $table = 'wordpress_plugins';

    protected $fillable = [
        'wordpress_installation_id',
        'slug',
        'name',
        'version',
        'author',
        'description',
        'plugin_uri',
        'author_uri',
        'is_active',
        'is_network_active',
        'status',
        'update_available',
        'requires',
        'auto_update',
        'last_checked',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_network_active' => 'boolean',
        'requires' => 'array',
        'auto_update' => 'boolean',
        'last_checked' => 'datetime',
    ];

    public function installation(): BelongsTo
    {
        return $this->belongsTo(WordPressInstallation::class, 'wordpress_installation_id');
    }

    public function hasUpdate(): bool
    {
        return !is_null($this->update_available);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithUpdates($query)
    {
        return $query->whereNotNull('update_available');
    }

    public function scopeAutoUpdate($query)
    {
        return $query->where('auto_update', true);
    }
}

<?php

namespace Modules\WordPress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordPressUpdate extends Model
{
    protected $table = 'wordpress_updates';

    protected $fillable = [
        'wordpress_installation_id',
        'update_type',
        'item_slug',
        'current_version',
        'new_version',
        'package_url',
        'status',
        'error_message',
        'is_auto_update',
        'scheduled_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_auto_update' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function installation(): BelongsTo
    {
        return $this->belongsTo(WordPressInstallation::class, 'wordpress_installation_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeCore($query)
    {
        return $query->where('update_type', 'core');
    }

    public function scopeThemes($query)
    {
        return $query->where('update_type', 'theme');
    }

    public function scopePlugins($query)
    {
        return $query->where('update_type', 'plugin');
    }
}

<?php

namespace Modules\WordPress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordPressTheme extends Model
{
    protected $table = 'wordpress_themes';

    protected $fillable = [
        'wordpress_installation_id',
        'slug',
        'name',
        'version',
        'author',
        'description',
        'theme_uri',
        'author_uri',
        'is_active',
        'template',
        'status',
        'update_available',
        'requires',
        'last_checked',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires' => 'array',
        'last_checked' => 'datetime',
    ];

    public function installation(): BelongsTo
    {
        return $this->belongsTo(WordPressInstallation::class, 'wordpress_installation_id');
    }

    public function parentTheme(): BelongsTo
    {
        return $this->belongsTo(WordPressTheme::class, 'template', 'slug');
    }

    public function hasUpdate(): bool
    {
        return ! is_null($this->update_available);
    }

    public function isChildTheme(): bool
    {
        return ! is_null($this->template);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithUpdates($query)
    {
        return $query->whereNotNull('update_available');
    }
}

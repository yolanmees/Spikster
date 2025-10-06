<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailForwarder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_id',
        'source',
        'destination',
        'keep_copy',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'keep_copy' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Get the site that owns the forwarder.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Check if this is a catch-all forwarder.
     */
    public function isCatchAll(): bool
    {
        return str_starts_with($this->source, '@');
    }

    /**
     * Get destination emails as array.
     */
    public function getDestinationsAttribute(): array
    {
        return array_map('trim', explode(',', $this->destination));
    }

    /**
     * Set destination emails from array.
     */
    public function setDestinationsAttribute(array $emails): void
    {
        $this->attributes['destination'] = implode(',', $emails);
    }

    /**
     * Scope a query to only include active forwarders.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include catch-all forwarders.
     */
    public function scopeCatchAll($query)
    {
        return $query->where('source', 'like', '@%');
    }
}

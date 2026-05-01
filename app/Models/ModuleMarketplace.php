<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleMarketplace extends Model
{
    use HasUuids;

    protected $fillable = [
        'package_name',
        'display_name',
        'slug',
        'description',
        'author',
        'author_url',
        'version',
        'minimum_core_version',
        'price',
        'license_type',
        'repository_url',
        'documentation_url',
        'demo_url',
        'downloads',
        'rating',
        'rating_count',
        'is_verified',
        'is_active',
        'screenshots',
        'tags',
        'compatibility',
        'last_updated',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'downloads' => 'integer',
        'rating_count' => 'integer',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'screenshots' => 'array',
        'tags' => 'array',
        'compatibility' => 'array',
        'last_updated' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'package_name', 'alias');
    }

    public function isFree(): bool
    {
        return $this->price == 0 || $this->license_type === 'free' || $this->license_type === 'open-source';
    }

    public function isPaid(): bool
    {
        return $this->price > 0 && $this->license_type === 'paid';
    }

    public function isFreemium(): bool
    {
        return $this->license_type === 'freemium';
    }

    public function isCompatible(string $coreVersion): bool
    {
        if (! $this->minimum_core_version) {
            return true;
        }

        return version_compare($coreVersion, $this->minimum_core_version, '>=');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeFree($query)
    {
        return $query->where('price', 0)->orWhere('license_type', 'free')->orWhere('license_type', 'open-source');
    }

    public function scopePaid($query)
    {
        return $query->where('price', '>', 0)->where('license_type', 'paid');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('downloads', 'desc');
    }

    public function scopeTopRated($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }
}

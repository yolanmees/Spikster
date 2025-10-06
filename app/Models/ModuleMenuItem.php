<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleMenuItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_id',
        'parent_id',
        'title',
        'route_name',
        'route_params',
        'url',
        'icon',
        'icon_type',
        'permission',
        'order_index',
        'is_active',
        'is_external',
        'target',
        'badge_type',
        'badge_source',
        'badge_color',
        'menu_location',
    ];

    protected $casts = [
        'route_params' => 'array',
        'order_index' => 'integer',
        'is_active' => 'boolean',
        'is_external' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ModuleMenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ModuleMenuItem::class, 'parent_id')->ordered();
    }

    public function getUrl(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->route_name) {
            return route($this->route_name, $this->route_params ?? []);
        }

        return '#';
    }

    public function getBadgeValue()
    {
        if ($this->badge_type === 'none' || !$this->badge_source) {
            return null;
        }

        // badge_source format: "ClassName@method" or "ClassName::method"
        if (str_contains($this->badge_source, '@')) {
            [$class, $method] = explode('@', $this->badge_source);
        } elseif (str_contains($this->badge_source, '::')) {
            [$class, $method] = explode('::', $this->badge_source);
        } else {
            return null;
        }

        if (!class_exists($class) || !method_exists($class, $method)) {
            return null;
        }

        return app($class)->$method();
    }

    public function hasPermission(): bool
    {
        if (!$this->permission) {
            return true;
        }

        return auth()->user()?->can($this->permission) ?? false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('menu_location', $location);
    }

    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('title');
    }

    public function scopeWithChildren($query)
    {
        return $query->with(['children' => function ($q) {
            $q->active()->ordered();
        }]);
    }
}

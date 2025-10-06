<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleWidget extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_id',
        'widget_key',
        'widget_name',
        'component_class',
        'title',
        'description',
        'icon',
        'width',
        'height',
        'order_index',
        'is_active',
        'permission',
        'config',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function hasPermission(): bool
    {
        if (!$this->permission) {
            return true;
        }

        return auth()->user()?->can($this->permission) ?? false;
    }

    public function getWidthClass(): string
    {
        return match ($this->width) {
            'quarter' => 'col-span-12 md:col-span-6 lg:col-span-3',
            'half' => 'col-span-12 md:col-span-6',
            'three-quarter' => 'col-span-12 md:col-span-6 lg:col-span-9',
            'full' => 'col-span-12',
            default => 'col-span-12 md:col-span-6',
        };
    }

    public function getHeightClass(): string
    {
        return match ($this->height) {
            'small' => 'h-48',
            'medium' => 'h-64',
            'large' => 'h-96',
            'auto' => 'h-auto',
            default => 'h-64',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('title');
    }
}

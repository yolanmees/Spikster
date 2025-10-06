<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'alias',
        'description',
        'version',
        'author',
        'category',
        'icon',
        'color',
        'priority',
        'is_active',
        'is_core',
        'is_installed',
        'installed_at',
        'installed_by',
        'metadata',
        'health_status',
        'health_data',
        'last_checked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'is_installed' => 'boolean',
        'installed_at' => 'datetime',
        'metadata' => 'array',
        'health_data' => 'array',
        'last_checked_at' => 'datetime',
    ];

    public function installedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ModuleSetting::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(ModuleDependency::class);
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(ModuleDependency::class, 'required_module_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(ModuleMenuItem::class);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(ModuleWidget::class);
    }

    public function hooks(): HasMany
    {
        return $this->hasMany(ModuleHook::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ModulePermission::class);
    }

    public function marketplace(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ModuleMarketplace::class, 'package_name', 'alias');
    }

    public function isHealthy(): bool
    {
        return $this->health_status === 'healthy';
    }

    public function hasWarnings(): bool
    {
        return $this->health_status === 'warning';
    }

    public function hasErrors(): bool
    {
        return $this->health_status === 'error';
    }

    public function canBeDisabled(): bool
    {
        if ($this->is_core) {
            return false;
        }

        // Check if other active modules depend on this one
        return !$this->dependents()->whereHas('module', function ($query) {
            $query->where('is_active', true);
        })->exists();
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match ($setting->setting_type) {
            'boolean' => (bool) $setting->setting_value,
            'integer' => (int) $setting->setting_value,
            'float' => (float) $setting->setting_value,
            'json', 'array' => json_decode($setting->setting_value, true),
            default => $setting->setting_value,
        };
    }

    public function setSetting(string $key, $value, string $type = 'string', bool $isEncrypted = false): void
    {
        $settingValue = match ($type) {
            'json', 'array' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        if ($isEncrypted) {
            $settingValue = encrypt($settingValue);
        }

        $this->settings()->updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $settingValue,
                'setting_type' => $type,
                'is_encrypted' => $isEncrypted,
            ]
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInstalled($query)
    {
        return $query->where('is_installed', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority')->orderBy('name');
    }
}

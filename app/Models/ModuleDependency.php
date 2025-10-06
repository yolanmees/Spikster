<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleDependency extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_id',
        'required_module_id',
        'required_package',
        'required_version',
        'dependency_type',
        'is_satisfied',
    ];

    protected $casts = [
        'is_satisfied' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function requiredModule(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'required_module_id');
    }

    public function checkSatisfied(): bool
    {
        return match ($this->dependency_type) {
            'module' => $this->checkModuleDependency(),
            'package' => $this->checkPackageDependency(),
            'php' => $this->checkPhpVersion(),
            'extension' => $this->checkPhpExtension(),
            default => false,
        };
    }

    protected function checkModuleDependency(): bool
    {
        if (!$this->requiredModule) {
            return false;
        }

        if (!$this->requiredModule->is_installed) {
            return false;
        }

        if ($this->required_version) {
            return version_compare(
                $this->requiredModule->version,
                $this->required_version,
                '>='
            );
        }

        return true;
    }

    protected function checkPackageDependency(): bool
    {
        if (!$this->required_package) {
            return false;
        }

        $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);
        
        foreach ($composerLock['packages'] ?? [] as $package) {
            if ($package['name'] === $this->required_package) {
                if ($this->required_version) {
                    return version_compare(
                        $package['version'],
                        $this->required_version,
                        '>='
                    );
                }
                return true;
            }
        }

        return false;
    }

    protected function checkPhpVersion(): bool
    {
        if (!$this->required_version) {
            return true;
        }

        return version_compare(PHP_VERSION, $this->required_version, '>=');
    }

    protected function checkPhpExtension(): bool
    {
        if (!$this->required_package) {
            return false;
        }

        return extension_loaded($this->required_package);
    }

    public function scopeUnsatisfied($query)
    {
        return $query->where('is_satisfied', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('dependency_type', $type);
    }
}

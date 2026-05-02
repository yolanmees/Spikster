<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleDependency;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module as ModuleFacade;

class ModuleRegistry
{
    /**
     * Verify a module's cryptographic signature.
     *
     * Modules can be signed with a private key. The signature is stored in the
     * module.json file as a "signature" field. This method verifies it against
     * the configured public key.
     *
     * If no public key is configured, unsigned modules are allowed with a warning.
     * If a public key is configured, unsigned modules are rejected.
     */
    public function verifySignature(string $modulePath): bool
    {
        $publicKey = config('modules.signing_public_key');

        if (empty($publicKey)) {
            // No signing key configured — allow unsigned modules but log a warning
            Log::warning('Module signing is not configured. All modules are allowed without signature verification.');

            return true;
        }

        $jsonPath = $modulePath.'/module.json';

        if (! File::exists($jsonPath)) {
            return false;
        }

        $moduleJson = json_decode(File::get($jsonPath), true);

        if (! isset($moduleJson['signature'])) {
            Log::warning('Unsigned module detected at: '.$modulePath);

            return false;
        }

        $signature = base64_decode($moduleJson['signature']);
        $payload = $this->getSignablePayload($moduleJson);

        $result = openssl_verify($payload, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if (! $result) {
            Log::error('Module signature verification failed at: '.$modulePath);

            return false;
        }

        return true;
    }

    /**
     * Get the payload to sign from module.json.
     * Excludes the signature field itself and any metadata.
     */
    protected function getSignablePayload(array $moduleJson): string
    {
        $fields = array_diff_key($moduleJson, array_flip(['signature', 'metadata']));
        ksort($fields);

        return json_encode($fields, JSON_UNESCAPED_SLASHES);
    }

    public function discover(): array
    public function discover(): array
    {
        $discovered = [];
        $modules = ModuleFacade::all();

        foreach ($modules as $module) {
            $moduleJson = $this->getModuleJson($module->getPath());

            if ($moduleJson) {
                $discovered[] = $moduleJson;
            }
        }

        return $discovered;
    }

    public function register(string $alias): Module
    {
        $nwidartModule = ModuleFacade::find($alias);

        if (! $nwidartModule) {
            throw new \Exception("Module {$alias} not found in filesystem");
        }

        $modulePath = $nwidartModule->getPath();
        $moduleJson = $this->getModuleJson($modulePath);

        if (! $moduleJson) {
            throw new \Exception("Module {$alias} has no valid module.json");
        }

        // Verify module signature if signing is enabled
        if (! $this->verifySignature($modulePath)) {
            throw new \Exception("Module {$alias} failed signature verification. The module may be tampered with or unsigned.");
        }

        $module = Module::updateOrCreate(
            ['alias' => $alias],
            [
                'name' => $moduleJson['name'] ?? $alias,
                'description' => $moduleJson['description'] ?? null,
                'version' => $moduleJson['version'] ?? '1.0.0',
                'author' => $moduleJson['author'] ?? null,
                'category' => $moduleJson['category'] ?? 'general',
                'icon' => $moduleJson['icon'] ?? null,
                'color' => $moduleJson['color'] ?? null,
                'priority' => $moduleJson['priority'] ?? 0,
                'is_core' => $moduleJson['is_core'] ?? false,
                'is_installed' => true,
                'installed_at' => now(),
                'installed_by' => auth()->id(),
                'metadata' => $moduleJson,
            ]
        );

        // Register dependencies
        if (isset($moduleJson['dependencies'])) {
            $this->registerDependencies($module, $moduleJson['dependencies']);
        }

        return $module;
    }

    protected function registerDependencies(Module $module, array $dependencies): void
    {
        foreach ($dependencies as $dependency) {
            ModuleDependency::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'dependency_type' => $dependency['type'] ?? 'module',
                    'required_package' => $dependency['package'] ?? null,
                    'required_module_id' => $dependency['module'] ?? null,
                ],
                [
                    'required_version' => $dependency['version'] ?? null,
                    'is_satisfied' => false, // Will be checked separately
                ]
            );
        }
    }

    public function enable(Module $module): bool
    {
        if ($module->is_active) {
            return true;
        }

        // Check dependencies
        $unsatisfied = $this->checkDependencies($module);

        if (! empty($unsatisfied)) {
            throw new \Exception(
                'Cannot enable module. Unsatisfied dependencies: '.
                implode(', ', $unsatisfied)
            );
        }

        $module->update([
            'is_active' => true,
            'health_status' => 'healthy',
        ]);

        // Enable in nWidart
        ModuleFacade::enable($module->alias);

        return true;
    }

    public function disable(Module $module): bool
    {
        if (! $module->canBeDisabled()) {
            throw new \Exception('Cannot disable this module. Other modules depend on it or it is a core module.');
        }

        $module->update(['is_active' => false]);

        // Disable in nWidart
        ModuleFacade::disable($module->alias);

        return true;
    }

    public function checkDependencies(Module $module): array
    {
        $unsatisfied = [];

        foreach ($module->dependencies as $dependency) {
            $isSatisfied = $dependency->checkSatisfied();
            $dependency->update(['is_satisfied' => $isSatisfied]);

            if (! $isSatisfied) {
                $unsatisfied[] = match ($dependency->dependency_type) {
                    'module' => "Module: {$dependency->requiredModule?->name}",
                    'package' => "Package: {$dependency->required_package}",
                    'php' => "PHP version: {$dependency->required_version}",
                    'extension' => "PHP extension: {$dependency->required_package}",
                    default => 'Unknown dependency',
                };
            }
        }

        return $unsatisfied;
    }

    public function checkHealth(Module $module): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
        ];

        // Check if module files exist
        $nwidartModule = ModuleFacade::find($module->alias);
        if (! $nwidartModule) {
            $health['status'] = 'error';
            $health['checks'][] = 'Module files not found';
        }

        // Check dependencies
        $unsatisfied = $this->checkDependencies($module);
        if (! empty($unsatisfied)) {
            $health['status'] = 'warning';
            $health['checks'][] = 'Unsatisfied dependencies: '.implode(', ', $unsatisfied);
        }

        // Check if service provider exists
        $providerPath = $nwidartModule?->getPath().'/Providers/'.studly_case($module->alias).'ServiceProvider.php';
        if ($nwidartModule && ! File::exists($providerPath)) {
            $health['status'] = 'warning';
            $health['checks'][] = 'Service provider not found';
        }

        $module->update([
            'health_status' => $health['status'],
            'health_data' => $health['checks'],
            'last_checked_at' => now(),
        ]);

        return $health;
    }

    protected function getModuleJson(string $modulePath): ?array
    {
        $jsonPath = $modulePath.'/module.json';

        if (! File::exists($jsonPath)) {
            return null;
        }

        return json_decode(File::get($jsonPath), true);
    }
}

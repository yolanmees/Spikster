<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleHook;
use Illuminate\Support\Collection;

class ModuleHookManager
{
    protected array $hooks = [];

    public function doAction(string $hookName, ...$args): void
    {
        $hooks = $this->getHooks($hookName, 'action');

        foreach ($hooks as $hook) {
            $hook->execute(...$args);
        }
    }

    public function applyFilter(string $hookName, $value, ...$args): mixed
    {
        $hooks = $this->getHooks($hookName, 'filter');
        
        array_unshift($args, $value);

        foreach ($hooks as $hook) {
            $result = $hook->execute(...$args);
            
            if ($result !== null) {
                $value = $result;
                $args[0] = $value; // Update first arg for next filter
            }
        }

        return $value;
    }

    public function registerHook(Module $module, array $data): ModuleHook
    {
        return ModuleHook::create([
            'module_id' => $module->id,
            'hook_name' => $data['name'],
            'hook_type' => $data['type'], // 'action' or 'filter'
            'callback_class' => $data['class'],
            'callback_method' => $data['method'],
            'priority' => $data['priority'] ?? 10,
            'accepted_args' => $data['accepted_args'] ?? 1,
            'description' => $data['description'] ?? null,
        ]);
    }

    public function unregisterHooks(Module $module): void
    {
        ModuleHook::where('module_id', $module->id)->delete();
        
        // Clear cached hooks
        $this->hooks = [];
    }

    public function getHooks(string $hookName, ?string $type = null): Collection
    {
        $cacheKey = $hookName . ($type ? ":{$type}" : '');

        if (isset($this->hooks[$cacheKey])) {
            return collect($this->hooks[$cacheKey]);
        }

        $query = ModuleHook::query()
            ->whereHas('module', function ($q) {
                $q->where('is_active', true);
            })
            ->active()
            ->byHook($hookName)
            ->ordered();

        if ($type) {
            $query->where('hook_type', $type);
        }

        $hooks = $query->get();
        
        // Cache for this request
        $this->hooks[$cacheKey] = $hooks->toArray();

        return $hooks;
    }

    public function getAvailableHooks(): array
    {
        return [
            // Module lifecycle hooks
            'module.before_enable' => 'Before a module is enabled',
            'module.after_enable' => 'After a module is enabled',
            'module.before_disable' => 'Before a module is disabled',
            'module.after_disable' => 'After a module is disabled',
            'module.before_install' => 'Before a module is installed',
            'module.after_install' => 'After a module is installed',
            
            // UI hooks
            'dashboard.widgets' => 'Modify dashboard widgets',
            'menu.items' => 'Modify menu items',
            'settings.tabs' => 'Add settings tabs',
            
            // Server hooks
            'server.before_create' => 'Before a server is created',
            'server.after_create' => 'After a server is created',
            'server.before_delete' => 'Before a server is deleted',
            'server.after_delete' => 'After a server is deleted',
            
            // Site hooks
            'site.before_create' => 'Before a site is created',
            'site.after_create' => 'After a site is created',
            'site.before_deploy' => 'Before a site is deployed',
            'site.after_deploy' => 'After a site is deployed',
            
            // User hooks
            'user.login' => 'After user login',
            'user.logout' => 'After user logout',
            'user.registered' => 'After user registration',
        ];
    }

    public function hasHook(string $hookName): bool
    {
        return ModuleHook::byHook($hookName)->active()->exists();
    }

    public function clearCache(): void
    {
        $this->hooks = [];
    }
}

<?php

namespace Modules\Greeter\Providers;

use App\Models\Module;
use App\Services\ModuleHookManager;
use App\Services\ModuleMenuManager;
use App\Services\ModuleRegistry;
use App\Services\ModuleWidgetManager;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Greeter\Livewire\Greeter;
use Modules\Greeter\Services\GreeterService;
use Spatie\Permission\Models\Permission;

class GreeterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'greeter');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'greeter');

        $module = $this->registerModule();
        if (! $module) {
            return;
        }

        $module->menuItems()->delete();
        $module->widgets()->delete();
        $module->hooks()->delete();
        $module->permissions()->delete();

        $this->registerMenuItems($module);
        $this->registerPermissions($module);
        $this->registerWidgets($module);
        $this->registerHooks($module);
        $this->registerLivewireComponents();
    }

    public function register(): void {}

    protected function registerModule(): ?Module
    {
        try {
            return app(ModuleRegistry::class)->register('greeter');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function registerMenuItems(Module $module): void
    {
        app(ModuleMenuManager::class)->registerMenuItem($module, [
            'title' => 'Greeter',
            'route_name' => 'greeter.index',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'icon_type' => 'custom',
            'permission' => 'greeter.view',
            'order_index' => 90,
            'menu_location' => 'main',
        ]);
    }

    protected function registerPermissions(Module $module): void
    {
        $perms = ['greeter.view', 'greeter.manage'];
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        foreach ($perms as $perm) {
            $permission = Permission::findByName($perm);
            $module->permissions()->firstOrCreate([
                'permission_id' => $permission->id,
                'is_required' => false,
                'description' => match ($perm) {
                    'greeter.view' => 'View the Greeter module page and widget',
                    'greeter.manage' => 'Manage Greeter module settings',
                },
            ]);
        }
    }

    protected function registerWidgets(Module $module): void
    {
        app(ModuleWidgetManager::class)->registerWidget($module, [
            'key' => 'greeter_welcome',
            'name' => 'Welcome Widget',
            'component' => Greeter::class,
            'title' => 'Welcome',
            'description' => 'A friendly greeting widget for the dashboard',
            'icon' => '👋',
            'width' => 'half',
            'height' => 'small',
            'order_index' => 10,
            'permission' => 'greeter.view',
        ]);
    }

    protected function registerHooks(Module $module): void
    {
        app(ModuleHookManager::class)->registerHook($module, [
            'name' => 'greeter.greeting',
            'type' => 'filter',
            'class' => GreeterService::class,
            'method' => 'filterGreeting',
            'priority' => 10,
            'accepted_args' => 1,
            'description' => 'Filter the greeting message',
        ]);
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('greeter::greeter', Greeter::class);
    }
}

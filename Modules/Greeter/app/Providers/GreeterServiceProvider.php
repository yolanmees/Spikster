<?php

namespace Modules\Greeter\Providers;

use App\Services\ModuleHookManager;
use App\Services\ModuleMenuManager;
use App\Services\ModuleRegistry;
use App\Services\ModuleWidgetManager;
use Illuminate\Support\ServiceProvider;
use Modules\Greeter\Livewire\Greeter;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;

class GreeterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'greeter');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'greeter');
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'greeter');

        $this->registerModule();
        $this->registerMenuItems();
        $this->registerPermissions();
        $this->registerWidgets();
        $this->registerHooks();
        $this->registerLivewireComponents();
    }

    public function register(): void
    {
    }

    protected function registerModule(): void
    {
        app(ModuleRegistry::class)->register('greeter');
    }

    protected function registerMenuItems(): void
    {
        $module = app(ModuleRegistry::class)->getModule('greeter');
        if (! $module) {
            return;
        }

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

    protected function registerPermissions(): void
    {
        $perms = ['greeter.view', 'greeter.manage'];
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $module = app(ModuleRegistry::class)->getModule('greeter');
        if ($module) {
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
    }

    protected function registerWidgets(): void
    {
        $module = app(ModuleRegistry::class)->getModule('greeter');
        if (! $module) {
            return;
        }

        app(ModuleWidgetManager::class)->registerWidget($module, [
            'widget_key' => 'greeter_welcome',
            'widget_name' => 'Welcome Widget',
            'component_class' => \Modules\Greeter\Livewire\Greeter::class,
            'title' => 'Welcome',
            'description' => 'A friendly greeting widget for the dashboard',
            'icon' => '👋',
            'width' => 'half',
            'height' => 'small',
            'order_index' => 10,
            'permission' => 'greeter.view',
        ]);
    }

    protected function registerHooks(): void
    {
        $module = app(ModuleRegistry::class)->getModule('greeter');
        if (! $module) {
            return;
        }

        app(ModuleHookManager::class)->registerHook($module, [
            'name' => 'greeter.greeting',
            'type' => 'filter',
            'class' => \Modules\Greeter\Services\GreeterService::class,
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

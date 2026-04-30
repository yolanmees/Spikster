<?php

namespace Modules\WordPress\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class WordPressServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'WordPress';

    protected string $nameLower = 'wordpress';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        // Register Livewire components
        $this->registerLivewireComponents();

        // Register module in Spikster Module System
        $this->registerModule();
        $this->registerMenuItems();
        $this->registerPermissions();
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        if (!class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::component('wordpress::theme-manager', \Modules\WordPress\Livewire\ThemeManager::class);
        \Livewire\Livewire::component('wordpress::plugin-manager', \Modules\WordPress\Livewire\PluginManager::class);
        \Livewire\Livewire::component('wordpress::updates-manager', \Modules\WordPress\Livewire\UpdatesManager::class);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Register services
        $this->app->singleton(\Modules\WordPress\Services\WordPressInstallationService::class);
        $this->app->singleton(\Modules\WordPress\Services\WPCLIService::class);
        $this->app->singleton(\Modules\WordPress\Services\WordPressOrgService::class);
    }

    /**
     * Register this module in the Spikster Module System.
     */
    protected function registerModule(): void
    {
        if (!app()->bound(\App\Services\ModuleRegistry::class)) {
            return;
        }

        try {
            $registry = app(\App\Services\ModuleRegistry::class);
            $registry->register('wordpress');
        } catch (\Exception $e) {
            \Log::warning('Failed to register WordPress module: ' . $e->getMessage());
        }
    }

    /**
     * Register menu items for WordPress module.
     */
    protected function registerMenuItems(): void
    {
        if (!app()->bound(\App\Services\ModuleMenuManager::class)) {
            return;
        }

        try {
            $menuManager = app(\App\Services\ModuleMenuManager::class);
            $module = \App\Models\Module::where('alias', 'wordpress')->first();

            if (!$module) {
                return;
            }

            // Main WordPress menu item
            $menuManager->registerMenuItem($module, [
                'title' => 'WordPress',
                'route_name' => 'wordpress.index',
                'icon' => '<svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.158 12.786L9.46 20.625c.806.237 1.657.366 2.54.366 1.047 0 2.051-.181 2.986-.51a.51.51 0 01-.047-.093l-2.78-7.602zm-4.955-7.055c.587-.023.987-.068.987-.068.494-.058.436-.784-.058-.759 0 0-1.486.117-2.446.117-.9 0-2.417-.117-2.417-.117-.494-.025-.552.726-.058.759 0 0 .371.045.762.068l1.132 3.107-1.591 4.773-2.65-7.88c.587-.023.987-.068.987-.068.494-.058.436-.784-.058-.759 0 0-1.486.117-2.446.117L.585 5.25C1.879 3.315 4.301 2 7 2c1.927 0 3.68.736 5 1.942-.032-.002-.063-.007-.095-.007-.9 0-1.537.784-1.537 1.626 0 .755.437 1.394.901 2.15.369.61.801 1.394.801 2.526 0 .784-.303 1.69-.698 2.95l-.916 3.06-3.32-9.871zm12.19 1.054c.086.631.135 1.307.135 2.029 0 2.003-.375 4.257-1.502 7.076l-3.018 8.729c2.936-1.712 4.912-4.888 4.912-8.524 0-1.73-.445-3.355-1.226-4.761l-.301-.549zM12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>',
                'icon_type' => 'heroicon',
                'permission' => 'wordpress.view',
                'order_index' => 10,
                'menu_location' => 'main',
                'badge_type' => 'count',
                'badge_source' => \Modules\WordPress\Services\WordPressBadgeService::class . '@getPendingUpdatesCount',
                'badge_color' => 'blue',
            ]);

        } catch (\Exception $e) {
            \Log::warning('Failed to register WordPress menu items: ' . $e->getMessage());
        }
    }

    /**
     * Register permissions for WordPress module.
     */
    protected function registerPermissions(): void
    {
        if (!class_exists(\Spatie\Permission\Models\Permission::class)) {
            return;
        }

        try {
            $permissions = [
                'wordpress.view' => 'View WordPress installations',
                'wordpress.create' => 'Create WordPress installations',
                'wordpress.update' => 'Update WordPress installations',
                'wordpress.delete' => 'Delete WordPress installations',
                'wordpress.manage-themes' => 'Manage WordPress themes',
                'wordpress.manage-plugins' => 'Manage WordPress plugins',
                'wordpress.manage-updates' => 'Manage WordPress updates',
            ];

            foreach ($permissions as $name => $description) {
                \Spatie\Permission\Models\Permission::firstOrCreate(
                    ['name' => $name],
                    ['guard_name' => 'web']
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to register WordPress permissions: ' . $e->getMessage());
        }
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}

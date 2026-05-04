<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleAutoloadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register a PSR-4-style autoloader for all Modules
        // Modules\WordPress\Services\Foo → Modules/WordPress/app/Services/Foo.php
        spl_autoload_register(function (string $class) {
            if (! str_starts_with($class, 'Modules\\')) {
                return;
            }

            $withoutPrefix = substr($class, strlen('Modules\\'));
            $slashPos = strpos($withoutPrefix, '\\');
            if ($slashPos === false) {
                return;
            }

            $moduleName = substr($withoutPrefix, 0, $slashPos);
            $remainder  = substr($withoutPrefix, $slashPos + 1);
            $file       = base_path('Modules/' . $moduleName . '/app/' . str_replace('\\', '/', $remainder) . '.php');

            if (is_file($file)) {
                require_once $file;
            }
        }, prepend: true);
    }

    public function boot(): void
    {
        $modulesPath = base_path('Modules');
        if (! is_dir($modulesPath)) {
            return;
        }

        foreach (glob($modulesPath . '/*/module.json') as $manifestFile) {
            $meta = json_decode(file_get_contents($manifestFile), true);
            if (! is_array($meta)) {
                continue;
            }

            foreach ($meta['providers'] ?? [] as $providerClass) {
                try {
                    if (class_exists($providerClass)) {
                        $this->app->register($providerClass);
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Module provider registration failed: {$providerClass} — {$e->getMessage()}");
                }
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\ModuleMenuItem;
use App\Services\ModuleMenuManager;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class DiagnoseModuleMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:diagnose-menu {alias? : Module alias (e.g., wordpress)} {--fix : Automatically fix found issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose and fix module menu visibility issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moduleAlias = $this->argument('alias');
        $autoFix = $this->option('fix');

        if (!$moduleAlias) {
            $this->diagnoseAllMenuItems($autoFix);
        } else {
            $this->diagnoseModuleMenu($moduleAlias, $autoFix);
        }

        return 0;
    }

    /**
     * Diagnose all menu items
     */
    protected function diagnoseAllMenuItems(bool $autoFix = false): void
    {
        $this->info('Diagnosing all module menu items...');
        $this->newLine();

        $menuManager = app(ModuleMenuManager::class);
        $visibleItems = $menuManager->getMenuItems('main', false); // Don't check permissions
        $allMenuItems = ModuleMenuItem::byLocation('main')->get();

        $this->table(
            ['Title', 'Route', 'Module Active', 'Item Active', 'Location', 'Visible'],
            $allMenuItems->map(function ($item) use ($visibleItems) {
                return [
                    $item->title,
                    $item->route_name ?? $item->url ?? '-',
                    $item->module->is_active ? '✅' : '❌',
                    $item->is_active ? '✅' : '❌',
                    $item->menu_location,
                    $visibleItems->contains('id', $item->id) ? '✅' : '❌',
                ];
            })
        );

        if ($autoFix) {
            $this->newLine();
            $this->info('Auto-fixing issues...');
            
            foreach ($allMenuItems as $item) {
                if (!$item->module->is_active) {
                    $item->module->update(['is_active' => true]);
                    $this->info("✓ Activated module: {$item->module->name}");
                }
                
                if (!$item->is_active) {
                    $item->update(['is_active' => true]);
                    $this->info("✓ Activated menu item: {$item->title}");
                }
            }
        }
    }

    /**
     * Diagnose a specific module's menu
     */
    protected function diagnoseModuleMenu(string $alias, bool $autoFix = false): void
    {
        $this->info("Diagnosing menu for module: {$alias}");
        $this->newLine();

        // Find module
        $module = Module::where('alias', $alias)->first();
        
        if (!$module) {
            $this->error("❌ Module '{$alias}' not found in database!");
            $this->newLine();
            $this->info("Available modules:");
            Module::all()->each(fn($m) => $this->line("  - {$m->alias} ({$m->name})"));
            return;
        }

        // Module status
        $this->info('MODULE STATUS:');
        $this->line("  Name: {$module->name}");
        $this->line("  Alias: {$module->alias}");
        $this->line("  Active: " . ($module->is_active ? '✅ Yes' : '❌ No'));
        $this->line("  Installed: " . ($module->is_installed ? '✅ Yes' : '❌ No'));
        $this->newLine();

        // Menu items
        $menuItems = $module->menuItems()->byLocation('main')->get();
        
        if ($menuItems->isEmpty()) {
            $this->warn("⚠️  No menu items found for location 'main'");
            
            $allMenuItems = $module->menuItems;
            if ($allMenuItems->isNotEmpty()) {
                $this->info("Found {$allMenuItems->count()} menu items in other locations:");
                $allMenuItems->each(fn($item) => 
                    $this->line("  - {$item->title} (location: {$item->menu_location})")
                );
            }
            $this->newLine();
        } else {
            $this->info("MENU ITEMS ({$menuItems->count()}):");
            
            foreach ($menuItems as $item) {
                $this->newLine();
                $this->line("  Title: {$item->title}");
                $this->line("  Route: " . ($item->route_name ?? $item->url ?? '-'));
                $this->line("  Active: " . ($item->is_active ? '✅ Yes' : '❌ No'));
                $this->line("  Location: {$item->menu_location}");
                $this->line("  Permission: " . ($item->permission ?? 'None'));
                
                // Check permission
                if ($item->permission) {
                    $permission = Permission::where('name', $item->permission)->first();
                    $this->line("  Permission exists: " . ($permission ? '✅ Yes' : '❌ No'));
                    
                    if ($permission && auth()->check()) {
                        $hasPermission = auth()->user()->can($item->permission);
                        $this->line("  User has permission: " . ($hasPermission ? '✅ Yes' : '❌ No'));
                    }
                }
            }
            $this->newLine();
        }

        // Test visibility
        $menuManager = app(ModuleMenuManager::class);
        $visibleItems = $menuManager->getMenuItems('main');
        $moduleVisibleItems = $visibleItems->filter(fn($item) => $item->module_id === $module->id);
        
        $this->info('VISIBILITY:');
        $this->line("  Items that should be visible: {$menuItems->count()}");
        $this->line("  Items actually visible: {$moduleVisibleItems->count()}");
        $this->newLine();

        // Issues found
        $issues = [];
        
        if (!$module->is_active) {
            $issues[] = 'Module is not active';
        }
        
        foreach ($menuItems as $item) {
            if (!$item->is_active) {
                $issues[] = "Menu item '{$item->title}' is not active";
            }
            
            if ($item->permission) {
                $permission = Permission::where('name', $item->permission)->first();
                if (!$permission) {
                    $issues[] = "Permission '{$item->permission}' does not exist";
                }
            }
        }

        if (empty($issues)) {
            $this->info('✅ No issues found!');
        } else {
            $this->warn('⚠️  ISSUES FOUND:');
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }
            $this->newLine();

            if ($autoFix) {
                $this->info('Applying fixes...');
                
                if (!$module->is_active) {
                    $module->update(['is_active' => true]);
                    $this->info('  ✓ Activated module');
                }
                
                foreach ($menuItems as $item) {
                    if (!$item->is_active) {
                        $item->update(['is_active' => true]);
                        $this->info("  ✓ Activated menu item: {$item->title}");
                    }
                    
                    if ($item->permission) {
                        $permission = Permission::firstOrCreate(['name' => $item->permission]);
                        if ($permission->wasRecentlyCreated) {
                            $this->info("  ✓ Created permission: {$item->permission}");
                        }
                        
                        if (auth()->check() && !auth()->user()->can($item->permission)) {
                            auth()->user()->givePermissionTo($item->permission);
                            $this->info("  ✓ Granted permission to current user: {$item->permission}");
                        }
                    }
                }
                
                $this->newLine();
                $this->info('✅ All fixes applied! Menu should now be visible.');
            } else {
                $this->info('Run with --fix to automatically fix these issues:');
                $this->info("  php artisan module:diagnose-menu {$alias} --fix");
            }
        }
    }
}

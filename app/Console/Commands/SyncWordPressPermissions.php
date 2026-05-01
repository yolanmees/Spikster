<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncWordPressPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wordpress:sync-permissions 
                            {--assign-to-admin : Assign permissions to admin role} 
                            {--assign-to-user= : Assign permissions to specific user ID or email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync WordPress module permissions with Spatie Permission system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing WordPress permissions...');
        $this->newLine();

        // Define all WordPress permissions
        $permissions = [
            'wordpress.view' => 'View WordPress installations',
            'wordpress.create' => 'Create WordPress installations',
            'wordpress.update' => 'Update WordPress installations',
            'wordpress.delete' => 'Delete WordPress installations',
            'wordpress.manage-themes' => 'Manage WordPress themes',
            'wordpress.manage-plugins' => 'Manage WordPress plugins',
            'wordpress.manage-updates' => 'Manage WordPress updates',
        ];

        $created = 0;
        $existing = 0;

        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $description]
            );

            if ($permission->wasRecentlyCreated) {
                $this->info("✓ Created permission: {$name}");
                $created++;
            } else {
                $this->line("  Permission exists: {$name}");
                $existing++;
            }
        }

        $this->newLine();
        $this->info("Created: {$created} permissions");
        $this->info("Existing: {$existing} permissions");
        $this->newLine();

        // Assign to admin role if requested or if it exists
        if ($this->option('assign-to-admin')) {
            $adminRole = Role::where('name', 'admin')->orWhere('name', 'Admin')->first();

            if ($adminRole) {
                $this->info('Assigning permissions to admin role...');
                $adminRole->syncPermissions(array_keys($permissions));
                $this->info("✓ All WordPress permissions assigned to '{$adminRole->name}' role");
            } else {
                $this->warn('⚠️  Admin role not found. Creating it...');
                $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
                $adminRole->syncPermissions(array_keys($permissions));
                $this->info('✓ Created admin role and assigned WordPress permissions');
            }
            $this->newLine();
        }

        // Assign to specific user if requested
        if ($userIdOrEmail = $this->option('assign-to-user')) {
            // Try to find user by ID first, then by email
            $user = is_numeric($userIdOrEmail)
                ? User::find($userIdOrEmail)
                : User::where('email', $userIdOrEmail)->first();

            if ($user) {
                $this->info("Assigning permissions to user: {$user->email}");
                foreach (array_keys($permissions) as $permission) {
                    if (! $user->hasPermissionTo($permission)) {
                        $user->givePermissionTo($permission);
                        $this->line("  ✓ Granted: {$permission}");
                    } else {
                        $this->line("  Already has: {$permission}");
                    }
                }
                $this->info("✓ All WordPress permissions assigned to user: {$user->email}");
            } else {
                $this->error("✗ User '{$userIdOrEmail}' not found");
            }
            $this->newLine();
        }

        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('✓ Permission cache cleared');

        $this->newLine();

        // Show usage examples
        if (! $this->option('assign-to-admin') && ! $this->option('assign-to-user')) {
            $this->info('💡 To assign permissions, use one of these options:');
            $this->line('');
            $this->line('  Assign to admin role:');
            $this->line('    php artisan wordpress:sync-permissions --assign-to-admin');
            $this->line('');
            $this->line('  Assign to specific user by ID:');
            $this->line('    php artisan wordpress:sync-permissions --assign-to-user=1');
            $this->line('');
            $this->line('  Assign to specific user by email:');
            $this->line('    php artisan wordpress:sync-permissions --assign-to-user=admin@example.com');
            $this->newLine();
        }

        $this->info('✅ WordPress permissions synced successfully!');

        return 0;
    }
}

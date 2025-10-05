<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Server permissions
            'server.view',
            'server.create',
            'server.edit',
            'server.delete',
            'server.manage',

            // Site permissions
            'site.view',
            'site.create',
            'site.edit',
            'site.delete',
            'site.manage',

            // User permissions
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.manage',

            // Role permissions
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'role.manage',

            // Settings permissions
            'settings.view',
            'settings.edit',

            // Monitoring permissions
            'monitoring.view',
            'monitoring.manage',

            // Logs permissions
            'logs.view',
            'logs.download',

            // Database permissions
            'database.view',
            'database.create',
            'database.edit',
            'database.delete',

            // API permissions
            'api.access',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - has all permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - has most permissions except user/role management
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            'server.view', 'server.create', 'server.edit', 'server.delete', 'server.manage',
            'site.view', 'site.create', 'site.edit', 'site.delete', 'site.manage',
            'user.view',
            'settings.view', 'settings.edit',
            'monitoring.view', 'monitoring.manage',
            'logs.view', 'logs.download',
            'database.view', 'database.create', 'database.edit', 'database.delete',
            'api.access',
        ]);

        // Developer - can manage sites and databases
        $developer = Role::create(['name' => 'Developer']);
        $developer->givePermissionTo([
            'server.view',
            'site.view', 'site.create', 'site.edit', 'site.manage',
            'monitoring.view',
            'logs.view',
            'database.view', 'database.create', 'database.edit', 'database.delete',
            'api.access',
        ]);

        // Viewer - read-only access
        $viewer = Role::create(['name' => 'Viewer']);
        $viewer->givePermissionTo([
            'server.view',
            'site.view',
            'user.view',
            'monitoring.view',
            'logs.view',
            'database.view',
        ]);

        // Client - limited access
        $client = Role::create(['name' => 'Client']);
        $client->givePermissionTo([
            'site.view',
            'monitoring.view',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Roles created: Super Admin, Admin, Developer, Viewer, Client');
        $this->command->info('Total permissions: ' . count($permissions));
    }
}

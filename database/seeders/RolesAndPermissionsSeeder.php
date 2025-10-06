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

        // Create permissions with organized groups
        $permissions = [
            // Server permissions
            'server.view', 'server.create', 'server.edit', 'server.delete',
            'server.reboot', 'server.configure',

            // Site permissions
            'site.view', 'site.create', 'site.edit', 'site.delete',
            'site.configure', 'site.deploy',

            // User permissions
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'user.impersonate',

            // Role & Permission management
            'role.view', 'role.create', 'role.edit', 'role.delete',
            'permission.assign', 'permission.revoke',

            // Email permissions
            'email.view', 'email.create', 'email.edit', 'email.delete',
            'email.configure',

            // Backup permissions
            'backup.view', 'backup.create', 'backup.restore', 'backup.delete',
            'backup.configure',

            // FTP permissions
            'ftp.view', 'ftp.create', 'ftp.edit', 'ftp.delete',

            // Database permissions
            'database.view', 'database.create', 'database.edit', 'database.delete',

            // DNS permissions
            'dns.view', 'dns.create', 'dns.edit', 'dns.delete',

            // SSL permissions
            'ssl.view', 'ssl.create', 'ssl.delete',

            // Monitoring permissions
            'monitoring.view', 'monitoring.configure',

            // Audit Log permissions
            'audit.view', 'audit.export',

            // Settings permissions
            'settings.view', 'settings.edit',

            // API access
            'api.access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with specific permission sets

        // 1. Super Admin - God mode, all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin - Manages servers and users, no system settings
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            // Servers (assigned only)
            'server.view', 'server.edit', 'server.configure',

            // Sites (all operations)
            'site.view', 'site.create', 'site.edit', 'site.delete',
            'site.configure', 'site.deploy',

            // Users (Reseller & User only)
            'user.view', 'user.create', 'user.edit', 'user.delete',

            // Email
            'email.view', 'email.create', 'email.edit', 'email.delete', 'email.configure',

            // Backups
            'backup.view', 'backup.create', 'backup.restore', 'backup.delete',

            // FTP
            'ftp.view', 'ftp.create', 'ftp.edit', 'ftp.delete',

            // Databases
            'database.view', 'database.create', 'database.edit', 'database.delete',

            // DNS
            'dns.view', 'dns.create', 'dns.edit', 'dns.delete',

            // SSL
            'ssl.view', 'ssl.create', 'ssl.delete',

            // Monitoring
            'monitoring.view',

            // Audit logs (own actions)
            'audit.view',

            // API
            'api.access',
        ]);

        // 3. Reseller - Manages clients and sites with resource limits
        $reseller = Role::firstOrCreate(['name' => 'Reseller']);
        $reseller->syncPermissions([
            // Sites (own only)
            'site.view', 'site.create', 'site.edit', 'site.delete', 'site.configure',

            // Users (User role only - own clients)
            'user.view', 'user.create', 'user.edit', 'user.delete',

            // Email
            'email.view', 'email.create', 'email.edit', 'email.delete',

            // Backups
            'backup.view', 'backup.create', 'backup.restore',

            // FTP
            'ftp.view', 'ftp.create', 'ftp.edit', 'ftp.delete',

            // Databases
            'database.view', 'database.create', 'database.edit', 'database.delete',

            // DNS
            'dns.view', 'dns.create', 'dns.edit', 'dns.delete',

            // SSL
            'ssl.view', 'ssl.create',

            // API
            'api.access',
        ]);

        // 4. User - End user, assigned sites only
        $user = Role::firstOrCreate(['name' => 'User']);
        $user->syncPermissions([
            // Sites (assigned only)
            'site.view', 'site.edit',

            // Email
            'email.view', 'email.create', 'email.edit', 'email.delete',

            // Backups (view and create only)
            'backup.view', 'backup.create',

            // FTP
            'ftp.view', 'ftp.create', 'ftp.edit', 'ftp.delete',

            // Databases
            'database.view', 'database.create', 'database.edit',

            // DNS (view only)
            'dns.view',

            // SSL (view only)
            'ssl.view',

            // API
            'api.access',
        ]);

        $this->command->info('✅ Roles and permissions seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   Roles created: 4 (Super Admin, Admin, Reseller, User)');
        $this->command->info('   Permissions created: ' . count($permissions));
        $this->command->info('');
        $this->command->info('🔐 Role Permissions:');
        $this->command->info('   Super Admin: ' . $superAdmin->permissions->count() . ' (ALL)');
        $this->command->info('   Admin: ' . $admin->permissions->count());
        $this->command->info('   Reseller: ' . $reseller->permissions->count());
        $this->command->info('   User: ' . $user->permissions->count());
    }
}

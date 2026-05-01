<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssignRolesToExistingUsersSeeder extends Seeder
{
    /**
     * Assign roles to existing users.
     *
     * This seeder helps you assign roles to users that existed before
     * the role system was implemented.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if (! $superAdminRole) {
            $this->command->warn('Super Admin role not found. Run RolesAndPermissionsSeeder first.');

            return;
        }

        $configuredAdminEmail = (string) config('cipi.username');
        $adminCandidates = array_values(array_filter([
            $configuredAdminEmail,
            'administrator@localhost',
        ]));

        foreach ($adminCandidates as $adminEmail) {
            $adminUser = User::where('email', $adminEmail)->first();
            if ($adminUser) {
                $adminUser->syncRoles([$superAdminRole->name]);
                $this->command->info("Assigned Super Admin role to: {$adminUser->email}");
            }
        }

        // Fallback: ensure at least one super admin exists.
        if (! User::role($superAdminRole->name)->exists()) {
            $firstUser = User::first();
            if ($firstUser) {
                $firstUser->syncRoles([$superAdminRole->name]);
                $this->command->info("Assigned Super Admin role to fallback user: {$firstUser->email}");
            }
        }

        // Assign default end-user role to all users without roles.
        $usersWithoutRoles = User::doesntHave('roles')->get();
        $defaultRole = Role::where('name', 'Customer')->first() ?? Role::where('name', 'User')->first();

        if ($defaultRole) {
            foreach ($usersWithoutRoles as $user) {
                $user->assignRole($defaultRole);
                $this->command->info("Assigned {$defaultRole->name} role to: {$user->email}");
            }
        }

        $this->command->info('Role assignment completed!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

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
        // Get the first user and make them Super Admin
        $firstUser = User::first();
        if ($firstUser) {
            $superAdminRole = Role::where('name', 'Super Admin')->first();
            if ($superAdminRole) {
                $firstUser->assignRole($superAdminRole);
                $this->command->info("Assigned Super Admin role to: {$firstUser->email}");
            }
        }

        // Optional: Assign roles to other specific users by email
        // Example:
        // $user = User::where('email', 'admin@example.com')->first();
        // if ($user) {
        //     $user->assignRole('Admin');
        //     $this->command->info("Assigned Admin role to: {$user->email}");
        // }

        // Or assign default role to all users without roles
        $usersWithoutRoles = User::doesntHave('roles')->get();
        $viewerRole = Role::where('name', 'Viewer')->first();

        if ($viewerRole) {
            foreach ($usersWithoutRoles as $user) {
                $user->assignRole($viewerRole);
                $this->command->info("Assigned Viewer role to: {$user->email}");
            }
        }

        $this->command->info('Role assignment completed!');
    }
}

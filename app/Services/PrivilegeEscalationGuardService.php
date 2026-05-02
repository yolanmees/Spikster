<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PrivilegeEscalationGuardService
{
    public function checkBeforeRoleChange(User $actor, User $target, string $newRole): array
    {
        $issues = [];

        if ($actor->id === $target->id && $newRole !== $target->roles->first()?->name) {
            $issues[] = 'Users cannot change their own role (prevents accidental self-lockout)';
        }

        if ($target->hasRole('Super Admin') && ! $actor->hasRole('Super Admin')) {
            $issues[] = 'Only Super Admins can modify other Super Admin roles';
        }

        $actorRole = $actor->roles->first()?->name;
        $targetRole = $target->roles->first()?->name;
        $hierarchy = ['Super Admin' => 4, 'Admin' => 3, 'Reseller' => 2, 'Customer' => 1];

        if (($hierarchy[$actorRole] ?? 0) <= ($hierarchy[$targetRole] ?? 0) && $actorRole !== $targetRole) {
            $issues[] = 'Cannot modify roles of users with equal or higher privilege level';
        }

        return [
            'allowed' => empty($issues),
            'issues' => $issues,
        ];
    }

    public function checkBeforeDelete(User $actor, User $target): array
    {
        $issues = [];

        if ($actor->id === $target->id) {
            $issues[] = 'Users cannot delete their own account';
        }

        if ($target->hasRole('Super Admin') && ! $actor->hasRole('Super Admin')) {
            $issues[] = 'Only Super Admins can delete Super Admin accounts';
        }

        $adminCount = User::role('Super Admin')->count();
        if ($target->hasRole('Super Admin') && $adminCount <= 1) {
            $issues[] = 'Cannot delete the last Super Admin account';
        }

        return [
            'allowed' => empty($issues),
            'issues' => $issues,
        ];
    }

    public function checkBeforePermissionRevoke(User $actor, string $permission): array
    {
        $issues = [];

        $critical = ['server.create', 'server.delete', 'user.impersonate', 'role.create', 'permission.revoke'];

        if (in_array($permission, $critical) && ! $actor->hasRole('Super Admin')) {
            $issues[] = "Only Super Admins can revoke the '{$permission}' permission";
        }

        return [
            'allowed' => empty($issues),
            'issues' => $issues,
        ];
    }
}

<?php

namespace App\Policies;

use App\Models\Server;
use App\Models\User;

class ServerPolicy
{
    /**
     * All authenticated users can list servers.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * All authenticated users can view a server's details.
     */
    public function view(User $user, Server $server): bool
    {
        return true;
    }

    /**
     * Only verified users can provision new servers.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    /**
     * Only the server owner (or admin) may update it.
     */
    public function update(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Only the server owner (or admin) may delete — and only when no active sites exist.
     */
    public function delete(User $user, Server $server): bool
    {
        if (! ($user->is_admin || $server->user_id === $user->id)) {
            return false;
        }

        // Prevent deletion if server has active sites
        if ($server->sites()->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Only admins may restore soft-deleted servers.
     */
    public function restore(User $user, Server $server): bool
    {
        return $user->is_admin ?? false;
    }

    /**
     * Permanent deletion is never allowed via policy.
     */
    public function forceDelete(User $user, Server $server): bool
    {
        return false;
    }

    // ─── Server-level actions ─────────────────────────────────────────────────

    /**
     * Whether the user may restart system services on this server.
     */
    public function manageServices(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may reset the server's root/spikster password.
     */
    public function resetPassword(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may manage the panel domain/SSL.
     */
    public function managePanel(User $user, Server $server): bool
    {
        return $user->is_admin ?? false;
    }

    /**
     * Whether the user may install/remove packages.
     */
    public function managePackages(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may view server monitoring metrics.
     */
    public function viewMetrics(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may manage fail2ban (ban/unban/whitelist IPs).
     */
    public function manageFail2ban(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may run an SSH shell session.
     */
    public function shell(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may list/view sites on this server.
     */
    public function viewSites(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may view domains associated with this server.
     */
    public function viewDomains(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may create a database on the server.
     */
    public function createDatabase(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }

    /**
     * Whether the user may ping/probe the server.
     */
    public function ping(User $user, Server $server): bool
    {
        return $user->is_admin || $server->user_id === $user->id;
    }
}

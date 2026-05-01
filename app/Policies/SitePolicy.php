<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    /**
     * Users with site.view permission can list sites.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('site.view');
    }

    /**
     * Users with site.view permission or server ownership may view a site.
     */
    public function view(User $user, Site $site): bool
    {
        return $user->can('site.view') || $site->server?->user_id === $user->id;
    }

    /**
     * Only users with site.create permission can create sites.
     */
    public function create(User $user): bool
    {
        return $user->can('site.create');
    }

    /**
     * Users with site.edit permission or server ownership may update a site.
     */
    public function update(User $user, Site $site): bool
    {
        return $user->can('site.edit') || $site->server?->user_id === $user->id;
    }

    /**
     * The panel site cannot be deleted; all others require site.delete or ownership.
     */
    public function delete(User $user, Site $site): bool
    {
        if ($site->panel == 1) {
            return false;
        }

        return $user->can('site.delete') || $site->server?->user_id === $user->id;
    }

    /**
     * Only users with site.configure permission may restore soft-deleted sites.
     */
    public function restore(User $user, Site $site): bool
    {
        return $user->can('site.configure');
    }

    /**
     * Permanent deletion is never allowed via policy.
     */
    public function forceDelete(User $user, Site $site): bool
    {
        return false;
    }

    // ─── Site-level actions ───────────────────────────────────────────────────

    /**
     * Whether the user may enable/renew SSL for this site.
     * Panel sites are excluded — they use a separate panel domain flow.
     */
    public function manageSsl(User $user, Site $site): bool
    {
        if ($site->panel == 1) {
            return false;
        }

        return $user->can('ssl.create') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may trigger a git deployment.
     */
    public function deploy(User $user, Site $site): bool
    {
        if (empty($site->source)) {
            return false;
        }

        return $user->can('site.deploy') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage domain aliases for this site.
     */
    public function manageAliases(User $user, Site $site): bool
    {
        return $user->can('site.configure') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage FTP accounts for this site.
     */
    public function manageFtp(User $user, Site $site): bool
    {
        return $user->can('ftp.create') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage databases for this site.
     */
    public function manageDatabases(User $user, Site $site): bool
    {
        return $user->can('database.create') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage email accounts/forwarders/aliases for this site.
     */
    public function manageEmail(User $user, Site $site): bool
    {
        return $user->can('email.create') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage cron jobs for this site.
     */
    public function manageCron(User $user, Site $site): bool
    {
        return $user->can('site.configure') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage DNS records for this site.
     */
    public function manageDns(User $user, Site $site): bool
    {
        return $user->can('dns.create') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may view and manage backups.
     */
    public function manageBackups(User $user, Site $site): bool
    {
        return $user->can('backup.view') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may access the file manager.
     */
    public function manageFiles(User $user, Site $site): bool
    {
        return $user->can('site.configure') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage WordPress settings for this site.
     */
    public function manageWordPress(User $user, Site $site): bool
    {
        return $user->can('site.configure') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may manage Node.js settings for this site.
     */
    public function manageNodejs(User $user, Site $site): bool
    {
        return $user->can('site.configure') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may update site credentials (system/db passwords).
     */
    public function updateCredentials(User $user, Site $site): bool
    {
        return $user->can('site.configure') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may view site access/error logs.
     */
    public function viewLogs(User $user, Site $site): bool
    {
        return $user->can('site.view') || $site->server?->user_id === $user->id;
    }

    /**
     * Whether the user may install/manage Roundcube webmail.
     */
    public function installRoundcube(User $user, Site $site): bool
    {
        return $user->can('site.configure') || $site->server?->user_id === $user->id;
    }
}

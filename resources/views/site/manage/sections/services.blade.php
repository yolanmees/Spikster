<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-site-management-card
        title="MySQL Database"
        subtitle="Data"
        description="Create and manage databases, users, and credentials for this site."
        status="Ready"
        statusClass="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300"
        :href="route('site.database', $site_id)"
        buttonLabel="Open Database"
    >
        <x-slot name="icon">
            <x-icon icon="database" class="h-4 w-4 text-zinc-700 dark:text-zinc-300" />
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="DNS Records"
        subtitle="Network"
        description="Configure A, CNAME, TXT, and other DNS records for this domain."
        status="Active"
        statusClass="bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300"
        :href="route('site.dns', $site_id)"
        buttonLabel="Manage DNS"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-purple-700 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
            </svg>
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Email Accounts"
        subtitle="Communication"
        description="Manage mailboxes, forwarders, and email settings for this site."
        status="Enabled"
        statusClass="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
        :href="route('email.index', $site_id)"
        buttonLabel="Open Email"
    >
        <x-slot name="icon">
            <x-icon icon="info" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Backup and Restore"
        subtitle="Recovery"
        description="Create snapshots and restore backups when needed."
        status="Protected"
        statusClass="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
        :href="route('backups.index', $site_id)"
        buttonLabel="Open Backups"
    >
        <x-slot name="icon">
            <x-icon icon="folder" class="h-4 w-4 text-amber-600 dark:text-amber-400" />
        </x-slot>
    </x-site-management-card>
</div>

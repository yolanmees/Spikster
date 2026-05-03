<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-site-management-card
        title="Monitor"
        subtitle="Performance"
        description="Real-time CPU, memory, load and disk charts for this server."
        status="Live"
        statusClass="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300"
        :href="route('server.edit.section', ['server_id' => $server_id, 'section' => 'monitor'])"
        buttonLabel="Open Monitor"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Information"
        subtitle="Server details"
        description="Update server name, IP, provider, PHP version and system services."
        status="Core"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        :href="route('server.edit.section', ['server_id' => $server_id, 'section' => 'information'])"
        buttonLabel="Open Information"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-zinc-700 dark:text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
            </svg>
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Security"
        subtitle="Protection"
        description="Manage Fail2ban, blocked IPs and server health monitoring."
        status="Active"
        statusClass="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
        :href="route('server.edit.section', ['server_id' => $server_id, 'section' => 'security'])"
        buttonLabel="Open Security"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Tools"
        subtitle="Maintenance"
        description="Change PHP CLI version, edit cron jobs and reset server access."
        status="Manual"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        :href="route('server.edit.section', ['server_id' => $server_id, 'section' => 'tools'])"
        buttonLabel="Open Tools"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </x-slot>
    </x-site-management-card>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-site-management-card
        title="Configuration"
        subtitle="Site settings"
        description="Update domain, aliases, PHP version, and supervisor settings."
        status="Core"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        :href="route('site.edit.section', ['site_id' => $site_id, 'section' => 'configuration'])"
        buttonLabel="Open Configuration"
    >
        <x-slot name="icon">
            <x-icon icon="cog" class="h-4 w-4 text-zinc-700 dark:text-zinc-300" />
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Security"
        subtitle="Protection"
        description="Manage SSL, reset credentials, and run maintenance actions."
        status="Important"
        statusClass="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
        :href="route('site.edit.section', ['site_id' => $site_id, 'section' => 'security'])"
        buttonLabel="Open Security"
    >
        <x-slot name="icon">
            <x-icon icon="info" class="h-4 w-4 text-amber-600 dark:text-amber-400" />
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Integrations"
        subtitle="Git and automation"
        description="Configure repository deployment and WordPress management."
        status="Optional"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        :href="route('site.edit.section', ['site_id' => $site_id, 'section' => 'integrations'])"
        buttonLabel="Open Integrations"
    >
        <x-slot name="icon">
            <x-icon icon="server" class="h-4 w-4 text-zinc-600 dark:text-zinc-400" />
        </x-slot>
    </x-site-management-card>

    <x-site-management-card
        title="Services"
        subtitle="Runtime tools"
        description="Open DNS, email, databases, and backup management pages."
        status="Live"
        statusClass="bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300"
        :href="route('site.edit.section', ['site_id' => $site_id, 'section' => 'services'])"
        buttonLabel="Open Services"
    >
        <x-slot name="icon">
            <x-icon icon="globe" class="h-4 w-4 text-green-600 dark:text-green-400" />
        </x-slot>
    </x-site-management-card>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    {{-- Server details --}}
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                </svg>
                Server Information
            </div>
        </x-slot>
        <livewire:server.server-information :server_id="$server_id" />
    </x-card>

    {{-- System services --}}
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                System Services
            </div>
        </x-slot>
        <livewire:server.system-services :server_id="$server_id" />
    </x-card>

    {{-- Logs --}}
    <x-site-management-card
        title="Server Logs"
        subtitle="Diagnostics"
        description="Access and review system, error and access logs for debugging and monitoring."
        status="Available"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        :href="route('logs.index', ['server_id' => $server_id])"
        buttonLabel="Open Logs"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-zinc-700 dark:text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </x-slot>
    </x-site-management-card>

    {{-- Packages --}}
    <x-site-management-card
        title="Installed Packages"
        subtitle="Software"
        description="View and manage software packages installed on this server."
        status="Info"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        :href="route('server.packages-installed', ['server_id' => $server_id])"
        buttonLabel="Open Packages"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </x-slot>
    </x-site-management-card>
</div>

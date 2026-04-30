<div class="space-y-6">
    <x-flash-messages />

    {{-- Stats --}}
    @if (!empty($statistics))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-stat-card title="Total Jails" :value="$statistics['total_jails'] ?? 0" color="gray"
                icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>' />
            <x-stat-card title="Active Jails" :value="$statistics['jails_active'] ?? 0" color="green"
                icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' />
            <x-stat-card title="Banned IPs" :value="$statistics['total_banned_ips'] ?? 0" color="red"
                icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>' />
            <x-stat-card title="Total Bans" :value="$statistics['total_bans'] ?? 0" color="yellow"
                icon='<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>' />
        </div>
    @endif

    {{-- Jails Table --}}
    <x-card>
        <x-slot name="header">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Fail2ban Jails</span>
            <x-secondary-button wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh" size="sm">
                <span wire:loading.remove wire:target="refresh" class="inline-flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </span>
                <span wire:loading wire:target="refresh" class="inline-flex items-center gap-1"><x-wire-spinner size="sm" /> Refreshing...</span>
            </x-secondary-button>
        </x-slot>

        @if ($isLoading)
            <div class="flex items-center justify-center py-12 gap-3">
                <x-spinner size="lg" />
                <span class="text-sm text-gray-500 dark:text-gray-400">Loading jails...</span>
            </div>
        @elseif(empty($jails))
            <x-empty-state icon="shield" title="No jails configured" message="No Fail2ban jails are configured on this server." />
        @else
            <div class="-mx-6 -mb-6">
                <x-table-wrapper>
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Jail Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Currently Banned</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Total Banned</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Failed Attempts</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                            @foreach ($jails as $jail)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                        <span class="font-mono">{{ $jail['name'] }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if (($jail['current_banned'] ?? 0) > 0)
                                            <x-badge color="red" text="{{ $jail['current_banned'] }} IPs" />
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">0</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $jail['total_banned'] ?? 0 }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div>Current: <span class="font-medium text-gray-900 dark:text-white">{{ $jail['current_failed'] ?? 0 }}</span></div>
                                        <div class="text-xs">Total: {{ $jail['total_failed'] ?? 0 }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <x-badge color="green" text="Active" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-table-wrapper>
            </div>
        @endif
    </x-card>
</div>

<div class="space-y-5">
    <x-flash-messages />

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-label for="searchIp" value="Search IP" />
            <x-search-input model="searchIp" placeholder="192.168.1.100" id="searchIp" class="mt-1" />
        </div>
        <div>
            <x-label for="selectedJail" value="Filter by Jail" />
            <x-select model="selectedJail" id="selectedJail" class="mt-1 block w-full">
                <option value="all">All Jails</option>
                @if (isset($iptables[0]) && is_array($iptables[0]))
                    @php
                        $jails = array_unique(array_column(array_filter($iptables[0], fn($ip) => isset($ip[1])), 1));
                        sort($jails);
                    @endphp
                    @foreach ($jails as $jail)
                        <option value="{{ $jail }}">{{ $jail }}</option>
                    @endforeach
                @endif
            </x-select>
        </div>
    </div>

    {{-- Table --}}
    <x-card>
        <x-slot name="header">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Banned IPs</span>
            <x-secondary-button wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh">
                <span wire:loading.remove wire:target="refresh" class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </span>
                <span wire:loading wire:target="refresh" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Refreshing...</span>
            </x-secondary-button>
        </x-slot>

        <div class="-mx-6 -mb-6">
            <x-table-wrapper>
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">IP Address</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Jail</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Banned At</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                        @php $filteredIps = $this->getFilteredIptables(); @endphp

                        @if (count($filteredIps) > 0)
                            @foreach ($filteredIps as $ip)
                                @if (is_array($ip) && count($ip) >= 2)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                            <span class="font-mono">{{ $ip[0] ?? 'N/A' }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <x-badge color="blue" :text="$ip[1] ?? 'N/A'" />
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            @if (isset($ip[2]))
                                                {{ date('Y-m-d H:i:s', $ip[2]) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button
                                                wire:click="unbanIp('{{ $ip[0] }}', '{{ $ip[1] ?? null }}')"
                                                wire:confirm="Are you sure you want to unban {{ $ip[0] }}?"
                                                wire:loading.attr="disabled"
                                                wire:target="unbanIp('{{ $ip[0] }}')"
                                                class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium inline-flex items-center gap-1">
                                                <span wire:loading.remove wire:target="unbanIp('{{ $ip[0] }}')">Unban</span>
                                                <span wire:loading wire:target="unbanIp('{{ $ip[0] }}')"><x-wire-spinner size="sm" /></span>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4">
                                    <x-empty-state icon="shield" title="No banned IPs"
                                        :message="!empty($searchIp) || $selectedJail !== 'all'
                                            ? 'No banned IPs matching your filters.'
                                            : 'No banned IPs found. Your server is secure!'" />
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </x-table-wrapper>
        </div>
    </x-card>
</div>

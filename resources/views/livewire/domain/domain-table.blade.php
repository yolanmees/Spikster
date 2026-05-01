<div>
    <x-flash-messages />

    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"></i>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by domain name..."
                    class="h-10 w-full rounded-lg border border-zinc-200 bg-white pl-9 pr-3 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500">
            </div>
            <select wire:model.live="filterType"
                class="h-10 rounded-lg border border-zinc-200 bg-white px-3 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">
                <option value="">All domains</option>
                <option value="primary">Primary domains</option>
                <option value="alias">Aliases</option>
            </select>
        </div>
    </div>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">
                        <x-sort-button field="domain" label="Domain" :sort-field="$sortField" :sort-direction="$sortDirection" />
                    </th>
                    <th scope="col" class="hidden px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400 lg:table-cell">Type</th>
                    <th scope="col" class="hidden px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400 lg:table-cell">Site</th>
                    <th scope="col" class="hidden px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400 xl:table-cell">Server</th>
                    <th scope="col" class="hidden px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400 lg:table-cell">DNS Records</th>
                    <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                @forelse ($domains as $domain)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full {{ $domain->dnsRecords->count() > 0 ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $domain->domain }}</span>
                            </div>
                        </td>
                        <td class="hidden whitespace-nowrap px-5 py-4 lg:table-cell">
                            @if ($domain->is_primary)
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">Primary</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">Alias</span>
                            @endif
                        </td>
                        <td class="hidden whitespace-nowrap px-5 py-4 text-sm text-zinc-500 dark:text-zinc-400 lg:table-cell">
                            @if ($domain->site)
                                <a href="{{ route('site.edit', $domain->site_id) }}" class="text-purple-700 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                                    {{ $domain->site->domain }}
                                </a>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600">&mdash;</span>
                            @endif
                        </td>
                        <td class="hidden whitespace-nowrap px-5 py-4 text-sm text-zinc-500 dark:text-zinc-400 xl:table-cell">
                            @if ($domain->server)
                                <span class="font-medium">{{ $domain->server->name }}</span>
                                <span class="font-mono text-xs text-zinc-400 dark:text-zinc-500">({{ $domain->server->ip }})</span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600">&mdash;</span>
                            @endif
                        </td>
                        <td class="hidden whitespace-nowrap px-5 py-4 lg:table-cell">
                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $domain->dnsRecords->count() }} records
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <a href="{{ route('domain.show', $domain->domain_id) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-700/10 transition-colors">
                                <i data-lucide="settings-2" class="h-3.5 w-3.5"></i>
                                Manage DNS
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state icon="globe" :title="$search || $filterType ? 'No domains found' : 'No domains yet'"
                                :message="$search || $filterType ? 'Try adjusting your search or filter.' : 'Start by adding a new site with a domain.'" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <x-slot name="pagination">@if ($domains->hasPages()){{ $domains->links() }}@endif</x-slot>
    </x-table-wrapper>
</div>

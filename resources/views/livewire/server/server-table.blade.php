<div>
    <x-flash-messages />

    <div class="mb-4">
        <x-search-input model="search" placeholder="Search by name, IP or provider..." />
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                <thead class="bg-zinc-50 text-left text-xs font-medium uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3">
                            <x-sort-button field="name" label="Server" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th class="hidden px-5 py-3 lg:table-cell">
                            <x-sort-button field="ip" label="IP" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th class="hidden px-5 py-3 lg:table-cell">Provider</th>
                        <th class="hidden px-5 py-3 lg:table-cell">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                    @forelse ($servers as $server)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-950 text-xs font-bold text-white dark:bg-white dark:text-zinc-950">
                                        {{ strtoupper(substr($server->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $server->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $server->location ?? 'No location' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300 lg:table-cell">
                                <span class="font-mono text-xs">{{ $server->ip }}</span>
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-4 lg:table-cell">
                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                    {{ ucfirst($server->provider) }}
                                </span>
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-4 lg:table-cell">
                                @if ($server->isActive())
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-700/15 dark:text-emerald-300">Active</span>
                                @else
                                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">Not installed</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('server.edit', $server->server_id) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-medium hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors">
                                        <i data-lucide="settings-2" class="h-3.5 w-3.5"></i>
                                        Manage
                                    </a>
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800 transition-colors"
                                            aria-label="More options">
                                            <i data-lucide="more-horizontal" class="h-4 w-4 text-zinc-500"></i>
                                        </button>
                                        <div x-show="open"
                                             x-cloak
                                             @click.outside="open = false"
                                             class="absolute right-0 z-20 mt-1 w-44 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                            <a href="{{ route('server.edit', $server->server_id) }}"
                                                class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                                <i data-lucide="pencil" class="h-4 w-4 text-zinc-400"></i>
                                                Edit
                                            </a>
                                            <div class="my-1 h-px bg-zinc-100 dark:bg-zinc-800"></div>
                                            <button
                                                wire:click="confirmDelete('{{ $server->server_id }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="confirmDelete('{{ $server->server_id }}')"
                                                @click="open = false"
                                                type="button"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 transition-colors">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                <span wire:loading.remove wire:target="confirmDelete('{{ $server->server_id }}')">Delete</span>
                                                <span wire:loading wire:target="confirmDelete('{{ $server->server_id }}')"><x-wire-spinner size="sm" /></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="server" title="No servers found">
                                    @if ($search)
                                        No servers found for "{{ $search }}"
                                    @else
                                        Start by adding a new server.
                                    @endif
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($servers->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
                {{ $servers->links() }}
            </div>
        @endif
    </div>

    <x-delete-confirm-modal
        :show="$confirmingDeletion"
        title="Delete Server"
        message="Are you sure you want to delete this server? This action cannot be undone."
        confirm-action="delete"
        cancel-action="cancelDelete"
    />
</div>

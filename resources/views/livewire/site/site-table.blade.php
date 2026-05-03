<div @if($polling) wire:poll.5s @endif>
    <x-flash-messages />

    @if($polling)
        <div class="mb-4 flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-100 p-3 text-sm text-zinc-700 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-300">
            <svg class="h-4 w-4 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>Site is being created... this page refreshes automatically.</span>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="sm:col-span-2">
            <x-search-input model="search" placeholder="Search by domain or username..." />
        </div>
        <x-select model="filterPhp">
            <option value="">All PHP versions</option>
            <option value="7.4">PHP 7.4</option>
            <option value="8.0">PHP 8.0</option>
            <option value="8.1">PHP 8.1</option>
            <option value="8.2">PHP 8.2</option>
            <option value="8.3">PHP 8.3</option>
        </x-select>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                <thead class="bg-zinc-50 text-left text-xs font-medium uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3">
                            <x-sort-button field="domain" label="Domain" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th class="hidden px-5 py-3 lg:table-cell">Username</th>
                        <th class="hidden px-5 py-3 lg:table-cell">
                            <x-sort-button field="php" label="PHP" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th class="hidden px-5 py-3 lg:table-cell">Repository</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                    @forelse ($sites as $site)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-700/15 text-purple-700 dark:text-purple-300 text-xs font-bold">
                                        {{ strtoupper(substr($site->domain, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $site->domain }}</p>
                                        @if ($site->isPanel())
                                            <span class="mt-0.5 inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">Panel</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300 lg:table-cell">
                                <span class="font-mono text-xs">{{ $site->username }}</span>
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-4 lg:table-cell">
                                <span class="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">PHP {{ $site->php }}</span>
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-4 lg:table-cell">
                                @if ($site->hasRepository())
                                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-700 dark:text-emerald-400">
                                        <i data-lucide="git-branch" class="h-3.5 w-3.5"></i>
                                        Git
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">No repo</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('site.edit', $site->site_id) }}"
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
                                             class="absolute right-0 z-20 mt-1 w-48 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                            <a href="{{ route('site.edit', $site->site_id) }}"
                                                class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                                <i data-lucide="pencil" class="h-4 w-4 text-zinc-400"></i>
                                                Edit
                                            </a>
                                            @if (!$site->isPanel())
                                                <div class="my-1 h-px bg-zinc-100 dark:bg-zinc-800"></div>
                                                <button
                                                    wire:click="confirmDelete('{{ $site->site_id }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="confirmDelete('{{ $site->site_id }}')"
                                                    @click="open = false"
                                                    type="button"
                                                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 transition-colors">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                    <span wire:loading.remove wire:target="confirmDelete('{{ $site->site_id }}')">Delete</span>
                                                    <span wire:loading wire:target="confirmDelete('{{ $site->site_id }}')"><x-wire-spinner size="sm" /></span>
                                                </button>
                                            @else
                                                <div class="px-3 py-2 text-xs text-zinc-400 dark:text-zinc-500">Panel — cannot be deleted</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="globe" :title="$search || $filterPhp ? 'No sites found' : 'No sites yet'" :message="$search || $filterPhp ? 'No sites found with current filters.' : 'Start by adding a new site.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sites->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
                {{ $sites->links() }}
            </div>
        @endif
    </div>

    <x-delete-confirm-modal
        :show="$confirmingDeletion"
        title="Delete Site"
        message="Are you sure you want to delete this site? This action cannot be undone. All files, databases and configurations will be removed."
        confirm-action="delete"
        cancel-action="cancelDelete"
    />
</div>

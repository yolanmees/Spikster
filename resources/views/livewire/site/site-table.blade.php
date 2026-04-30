<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
    @endif

    @if (session()->has('error'))
        <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
    @endif

    <!-- Search and Filters -->
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Search -->
        <div class="sm:col-span-2">
            <x-text-input model="search" debounce="300" placeholder="Search by domain or username...">
                <x-slot name="icon">
                    <x-icon icon="search" class="h-5 w-5 text-gray-400" />
                </x-slot>
                <x-slot name="suffix">
                    <div wire:loading wire:target="search">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </x-slot>
            </x-text-input>
        </div>

        <!-- PHP Version Filter -->
        <x-select model="filterPhp">
            <option value="">All PHP versions</option>
            <option value="7.4">PHP 7.4</option>
            <option value="8.0">PHP 8.0</option>
            <option value="8.1">PHP 8.1</option>
            <option value="8.2">PHP 8.2</option>
            <option value="8.3">PHP 8.3</option>
        </x-select>
    </div>

    <!-- Table Container with Loading Overlay -->
    <div class="relative">
        <!-- Loading Overlay -->
        <div wire:loading.delay
            class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 flex items-center justify-center rounded-lg">
            <livewire:components.loading-spinner size="lg" color="blue" message="Loading..." />
        </div>

        <!-- Table -->
        <div class="mt-4 -mx-4 ring-1 ring-gray-300 sm:mx-0 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col"
                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">
                            <x-sort-button field="domain" label="Domain" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Username
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            <x-sort-button field="php" label="PHP" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Repository
                        </th>
                        <th scope="col" class="relative text-right py-3.5 pl-3 pr-4 sm:pr-6 dark:text-white">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                    @forelse ($sites as $site)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="flex items-center">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $site->domain }}
                                        </div>
                                        @if ($site->isPanel())
                                            <div class="mt-1">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                    Panel
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell dark:text-gray-300">
                                <span class="font-mono text-xs">{{ $site->username }}</span>
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                <x-badge color="indigo" text="PHP {{ $site->php }}" />
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                @if ($site->hasRepository())
                                    <span class="inline-flex items-center text-green-700 dark:text-green-400">
                                        <x-icon icon="check" class="mr-1.5 h-4 w-4" />
                                        <span class="text-xs">Git</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">No repo</span>
                                @endif
                            </td>
                            <td class="relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <x-action-button :href="route('site.edit', $site->site_id)">
                                    Manage
                                </x-action-button>
                                @if (!$site->isPanel())
                                    <x-danger-button wire:click="confirmDelete('{{ $site->site_id }}')" type="button"
                                        class="ml-2" wire:loading.attr="disabled" wire:target="confirmDelete">
                                        Delete
                                    </x-danger-button>
                                @else
                                    <span
                                        class="ml-2 inline-flex items-center px-2.5 py-1.5 text-xs text-gray-400 dark:text-gray-500">
                                        (Panel - cannot be deleted)
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="globe" :title="$search || $filterPhp ? 'No sites found' : 'No sites yet'" :message="$search || $filterPhp
                                    ? 'No sites found with current filters.'
                                    : 'Start by adding a new site.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($sites->hasPages())
            <div class="mt-4">
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

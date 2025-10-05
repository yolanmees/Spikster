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
            <x-text-input model="search" debounce="300" placeholder="Search by domain name...">
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

        <!-- Type Filter -->
        <x-select model="filterType">
            <option value="">All domains</option>
            <option value="primary">Primary domains</option>
            <option value="alias">Aliases</option>
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
                            <button wire:click="sortBy('domain')"
                                class="group inline-flex items-center hover:text-blue-600">
                                Domain
                                @if ($sortField === 'domain')
                                    @if ($sortDirection === 'asc')
                                        <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                        </svg>
                                    @else
                                        <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                        </svg>
                                    @endif
                                @endif
                            </button>
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Type
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Site
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Server
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            DNS Records
                        </th>
                        <th scope="col" class="relative text-right py-3.5 pl-3 pr-4 sm:pr-6 dark:text-white">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                    @forelse ($domains as $domain)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="flex items-center">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $domain->domain }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                @if ($domain->is_primary)
                                    <x-badge color="green" text="Primary" />
                                @else
                                    <x-badge color="gray" text="Alias" />
                                @endif
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell dark:text-gray-300">
                                @if ($domain->site)
                                    <a href="{{ route('site.edit', $domain->site_id) }}"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $domain->site->domain }}
                                    </a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell dark:text-gray-300">
                                @if ($domain->server)
                                    <span class="font-medium">{{ $domain->server->name }}</span>
                                    <span class="text-xs text-gray-400">({{ $domain->server->ip }})</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                <x-badge color="blue" :text="$domain->dnsRecords->count() . ' records'" />
                            </td>
                            <td class="relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <x-action-button :href="route('domain.show', $domain->domain_id)">
                                    Manage DNS
                                </x-action-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="globe" :title="$search || $filterType ? 'No domains found' : 'No domains yet'" :message="$search || $filterType
                                    ? 'No domains found with current filters.'
                                    : 'Start by adding a new site with a domain.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($domains->hasPages())
            <div class="mt-4">
                {{ $domains->links() }}
            </div>
        @endif
    </div>
</div>

<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
    @endif

    @if (session()->has('error'))
        <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
    @endif

    <!-- Search Bar -->
    <div class="mb-4">
        <x-text-input model="search" debounce="300" placeholder="Search by name, IP or provider...">
            <x-slot name="icon">
                <x-icon icon="search" class="h-5 w-5 text-gray-400" />
            </x-slot>
            <x-slot name="suffix">
                <div wire:loading wire:target="search">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
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
                            <x-sort-button field="name" label="Server" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            <x-sort-button field="ip" label="IP" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Provider
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Status
                        </th>
                        <th scope="col" class="relative text-right py-3.5 pl-3 pr-4 sm:pr-6 dark:text-white">
                            Acties
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                    @forelse ($servers as $server)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $server->name }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                    {{ $server->location ?? 'No location' }}
                                </div>
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell dark:text-gray-300">
                                <span class="font-mono">{{ $server->ip }}</span>
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell dark:text-gray-300">
                                <x-badge color="blue" :text="ucfirst($server->provider)" />
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                @if ($server->isActive())
                                    <x-badge color="green" text="Active" />
                                @else
                                    <x-badge color="gray" text="Not installed" />
                                @endif
                            </td>
                            <td class="relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <x-action-button :href="route('server.edit', $server->server_id)">
                                    Manage
                                </x-action-button>
                                <x-danger-button wire:click="confirmDelete('{{ $server->server_id }}')" type="button"
                                    class="ml-2">
                                    Delete
                                </x-danger-button>
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

        <!-- Pagination -->
        @if ($servers->hasPages())
            <div class="mt-4">
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

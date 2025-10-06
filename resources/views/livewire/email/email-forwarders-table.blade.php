<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
    @endif

    @if (session()->has('error'))
        <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
    @endif

    <!-- Header with Actions -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Forwarders</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage email forwarding rules for {{ $site->domain }}
            </p>
        </div>
        <button wire:click="create" type="button"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Forwarder
        </button>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <x-text-input model="search" debounce="300" placeholder="Search by source or destination...">
            <x-slot name="icon">
                <x-icon icon="search" class="h-5 w-5 text-gray-400" />
            </x-slot>
        </x-text-input>
    </div>

    <!-- Table -->
    <div class="relative">
        <div wire:loading.delay
            class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 flex items-center justify-center rounded-lg">
            <livewire:components.loading-spinner size="lg" color="blue" message="Loading..." />
        </div>

        <div class="mt-4 -mx-4 ring-1 ring-gray-300 sm:mx-0 sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col"
                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">
                            Source
                        </th>
                        <th scope="col"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">
                            Destination
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Type
                        </th>
                        <th scope="col" class="relative text-right py-3.5 pl-3 pr-4 sm:pr-6 dark:text-white">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                    @forelse ($forwarders as $forwarder)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    @if ($forwarder->is_catch_all)
                                        *@{{ $site - > domain }}
                                        <x-badge color="purple" text="Catch-All" class="ml-2" />
                                    @else
                                        {{ $forwarder->source }}@{{ $site - > domain }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-3.5 text-sm text-gray-900 dark:text-white">
                                <div class="max-w-md break-words">
                                    {{ str_replace(',', ', ', $forwarder->destination) }}
                                </div>
                                @if ($forwarder->keep_copy)
                                    <x-badge color="blue" text="Keep Copy" class="mt-1" />
                                @endif
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                @if ($forwarder->is_catch_all)
                                    <x-badge color="purple" text="Catch-All" />
                                @else
                                    <x-badge color="green" text="Forward" />
                                @endif
                            </td>
                            <td class="relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <x-danger-button wire:click="confirmDelete('{{ $forwarder->id }}')" type="button"
                                    wire:loading.attr="disabled" wire:target="confirmDelete">
                                    Delete
                                </x-danger-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="forward" :title="$search ? 'No forwarders found' : 'No forwarders yet'" :message="$search
                                    ? 'No forwarders found with current filters.'
                                    : 'Start by creating a new forwarder.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($forwarders->hasPages())
            <div class="mt-4">
                {{ $forwarders->links() }}
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div wire:click="closeModals" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:w-full sm:max-w-2xl">
                    <livewire:email.create-email-forwarder :site="$site" :key="'create-forwarder-' . now()" />
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if ($confirmingDeletion)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div wire:click="cancelDelete" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:w-full sm:max-w-lg">
                    <div class="px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Forwarder</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Are you sure you want to delete this forwarder? This action cannot be undone.
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <x-danger-button wire:click="delete" class="w-full sm:ml-3 sm:w-auto">
                            Delete
                        </x-danger-button>
                        <x-secondary-button wire:click="cancelDelete" class="mt-3 w-full sm:mt-0 sm:w-auto">
                            Cancel
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div>
    <x-flash-messages />

    <x-section-header title="Email Forwarders" subtitle="Manage email forwarding rules for {{ $site->domain }}">
        <x-slot name="actions">
            <x-primary-button wire:click="create">
                <x-icon icon="plus" class="h-4 w-4 -ml-1 mr-1.5" />
                Create Forwarder
            </x-primary-button>
        </x-slot>
    </x-section-header>

    <div class="mb-4">
        <x-search-input model="search" placeholder="Search by source or destination..." />
    </div>

    <x-table-wrapper>
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
                                    wire:loading.attr="disabled" wire:target="confirmDelete('{{ $forwarder->id }}')">
                                    <span wire:loading.remove wire:target="confirmDelete('{{ $forwarder->id }}')">Delete</span>
                                    <span wire:loading wire:target="confirmDelete('{{ $forwarder->id }}')"><x-wire-spinner size="sm" /></span>
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

        <x-slot name="pagination">@if ($forwarders->hasPages()){{ $forwarders->links() }}@endif</x-slot>
    </x-table-wrapper>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <x-modal title="Create Email Forwarder" max-width="2xl">
            <livewire:email.create-email-forwarder :site="$site" :key="'create-forwarder-' . now()" />
        </x-modal>
    @endif

    <x-delete-confirm-modal
        :show="$confirmingDeletion"
        title="Delete Forwarder"
        message="Are you sure you want to delete this forwarder? This action cannot be undone."
        confirm-action="delete"
        cancel-action="cancelDelete"
    />
</div>

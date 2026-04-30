<div>
    <x-flash-messages />

    <x-section-header title="Email Accounts" subtitle="Manage email accounts for {{ $site->domain }}">
        <x-slot name="actions">
            <x-primary-button wire:click="create">
                <x-icon icon="plus" class="h-4 w-4 -ml-1 mr-1.5" />
                Create Account
            </x-primary-button>
        </x-slot>
    </x-section-header>

    <!-- Search and Filters -->
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Search -->
        <div class="sm:col-span-2">
            <x-search-input model="search" placeholder="Search by email or username..." />
        </div>

        <!-- Active Filter -->
        <x-select model="filterActive">
            <option value="">All Accounts</option>
            <option value="1">Active Only</option>
            <option value="0">Disabled Only</option>
        </x-select>
    </div>

    <x-table-wrapper>
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col"
                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">
                            <x-sort-button field="email" label="Email Address" :sort-field="$sortField" :sort-direction="$sortDirection" />
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Quota Usage
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Aliases
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white lg:table-cell">
                            Status
                        </th>
                        <th scope="col" class="relative text-right py-3.5 pl-3 pr-4 sm:pr-6 dark:text-white">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                    @forelse ($accounts as $account)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="flex items-center">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $account->email }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Created {{ $account->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                @if ($account->quota_info)
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span
                                                class="text-gray-500 dark:text-gray-400">{{ $account->quota_info['used_formatted'] }}
                                                / {{ $account->quota_info['quota_formatted'] }}</span>
                                            <span
                                                class="font-medium {{ $account->quota_info['percentage'] >= 95 ? 'text-red-600' : ($account->quota_info['percentage'] >= 80 ? 'text-yellow-600' : 'text-green-600') }}">
                                                {{ number_format($account->quota_info['percentage'], 1) }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                            <div class="h-2 rounded-full transition-all {{ $account->quota_info['percentage'] >= 95 ? 'bg-red-600' : ($account->quota_info['percentage'] >= 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                                style="width: {{ min($account->quota_info['percentage'], 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                @if ($account->aliases_count > 0)
                                    <x-badge color="blue" text="{{ $account->aliases_count }} alias(es)" />
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">No aliases</span>
                                @endif
                            </td>
                            <td class="hidden px-3 py-3.5 text-sm lg:table-cell">
                                <button wire:click="toggleActive('{{ $account->id }}')" type="button"
                                    class="inline-flex items-center">
                                    @if ($account->is_active)
                                        <x-badge color="green" text="Active" />
                                    @else
                                        <x-badge color="red" text="Disabled" />
                                    @endif
                                </button>
                            </td>
                            <td class="relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                                <x-action-button wire:click="edit('{{ $account->id }}')" type="button">
                                    Edit
                                </x-action-button>
                                <x-danger-button wire:click="confirmDelete('{{ $account->id }}')" type="button"
                                    wire:loading.attr="disabled" wire:target="confirmDelete">
                                    Delete
                                </x-danger-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty-state icon="envelope" :title="$search ? 'No email accounts found' : 'No email accounts yet'" :message="$search
                                    ? 'No email accounts found with current filters.'
                                    : 'Start by creating a new email account.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-slot name="pagination">@if ($accounts->hasPages()){{ $accounts->links() }}@endif</x-slot>
    </x-table-wrapper>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <x-modal title="Create Email Account" max-width="2xl">
            <livewire:email.create-email-account :site="$site" :key="'create-account-' . now()" />
        </x-modal>
    @endif

    {{-- Edit Modal --}}
    @if ($showEditModal && $editingAccount)
        <x-modal title="Edit Email Account" max-width="2xl">
            <livewire:email.edit-email-account :account-id="$editingAccount" :key="'edit-account-' . $editingAccount" />
        </x-modal>
    @endif

    <x-delete-confirm-modal
        :show="$confirmingDeletion"
        title="Delete Email Account"
        message="Are you sure you want to delete this email account? This action cannot be undone. All emails will be permanently removed from the server."
        confirm-action="delete"
        cancel-action="cancelDelete"
    />
</div>

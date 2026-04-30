<div>
    <x-flash-messages />

    <div class="mb-4">
        <x-search-input model="search" placeholder="Search by name, IP or provider..." />
    </div>

    <x-table-wrapper>
        <x-slot name="pagination">@if ($servers->hasPages()){{ $servers->links() }}@endif</x-slot>
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
    </x-table-wrapper>

    <x-delete-confirm-modal
        :show="$confirmingDeletion"
        title="Delete Server"
        message="Are you sure you want to delete this server? This action cannot be undone."
        confirm-action="delete"
        cancel-action="cancelDelete"
    />
</div>

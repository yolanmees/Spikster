<div>
    <x-flash-messages />

    <!-- Search and Filters -->
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Search -->
        <div class="sm:col-span-2">
            <x-search-input model="search" placeholder="Search by domain name..." />
        </div>

        <!-- Type Filter -->
        <x-select model="filterType">
            <option value="">All domains</option>
            <option value="primary">Primary domains</option>
            <option value="alias">Aliases</option>
        </x-select>
    </div>

    <x-table-wrapper>
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col"
                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">
                            <x-sort-button field="domain" label="Domain" :sort-field="$sortField" :sort-direction="$sortDirection" />
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

        <x-slot name="pagination">@if ($domains->hasPages()){{ $domains->links() }}@endif</x-slot>
    </x-table-wrapper>
</div>

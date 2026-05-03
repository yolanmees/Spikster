<div>
    <div class="mb-4">
        <x-search-input model="search" placeholder="Search packages..." />
    </div>

    <x-table-wrapper>
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Package</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                @if(isset($packages))
                    @forelse($packages as $package)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                {{ $package['package'] }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($package['installed'] == 'true')
                                    <x-badge color="green" text="Installed" />
                                @else
                                    <x-badge color="gray" text="Not installed" />
                                @endif
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                @if($package['installed'] == 'false')
                                    <button
                                        wire:click="install('{{ $package['package'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="install('{{ $package['package'] }}')"
                                        class="inline-flex items-center gap-1 text-zinc-600 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-300 font-medium">
                                        <span wire:loading.remove wire:target="install('{{ $package['package'] }}')">Install</span>
                                        <span wire:loading wire:target="install('{{ $package['package'] }}')">
                                            <x-wire-spinner size="sm" />
                                        </span>
                                    </button>
                                @else
                                    <button
                                        wire:click="uninstall('{{ $package['package'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="uninstall('{{ $package['package'] }}')"
                                        class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium">
                                        <span wire:loading.remove wire:target="uninstall('{{ $package['package'] }}')">Uninstall</span>
                                        <span wire:loading wire:target="uninstall('{{ $package['package'] }}')">
                                            <x-wire-spinner size="sm" />
                                        </span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-empty-state icon="package" title="No packages found">
                                    No packages available or matching your search.
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </x-table-wrapper>
</div>

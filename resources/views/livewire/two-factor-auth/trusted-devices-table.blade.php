<div class="space-y-4">
    <x-flash-messages />

    <x-card>
        <x-slot name="header">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Trusted Devices</span>
            @if (count($devices) > 0)
                <x-secondary-button wire:click="cleanupExpired" wire:loading.attr="disabled" wire:target="cleanupExpired">
                    <span wire:loading.remove wire:target="cleanupExpired">Cleanup Expired</span>
                    <span wire:loading wire:target="cleanupExpired"><x-wire-spinner size="sm" /></span>
                </x-secondary-button>
            @endif
        </x-slot>

        @if (count($devices) === 0)
            <x-empty-state icon="device" title="No trusted devices" message="No trusted devices found." />
        @else
            <div class="-mx-6 -mb-6">
                <x-table-wrapper>
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Device</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">IP Address</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Last Used</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Expires</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                            @foreach ($devices as $device)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $device['device_name'] ?? 'Unknown Device' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $device['browser'] }} • {{ $device['platform'] }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-mono text-gray-900 dark:text-white">
                                        {{ $device['ip_address'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($device['last_used_at'])->diffForHumans() }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($device['expires_at'])->diffForHumans() }}
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <button
                                            wire:click="removeDevice('{{ $device['id'] }}')"
                                            wire:confirm="Are you sure you want to remove this device?"
                                            wire:loading.attr="disabled"
                                            wire:target="removeDevice('{{ $device['id'] }}')"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium inline-flex items-center gap-1">
                                            <span wire:loading.remove wire:target="removeDevice('{{ $device['id'] }}')">Remove</span>
                                            <span wire:loading wire:target="removeDevice('{{ $device['id'] }}')"><x-wire-spinner size="sm" /></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-table-wrapper>
            </div>
        @endif
    </x-card>
</div>

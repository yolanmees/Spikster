<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Mailbox Quota</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Monitor and manage quota for {{ $account->email }}
        </p>
    </div>

    @if ($quotaUsage)
        <!-- Quota Statistics -->
        <div class="space-y-6">
            <!-- Usage Bar -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Storage Usage</span>
                    <span
                        class="text-sm font-medium {{ $quotaUsage['percentage'] >= 95 ? 'text-red-600 dark:text-red-400' : ($quotaUsage['percentage'] >= 80 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                        {{ number_format($quotaUsage['percentage'], 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                    <div class="{{ $this->getProgressBarColor() }} h-4 rounded-full transition-all flex items-center justify-end pr-2"
                        style="width: {{ min($quotaUsage['percentage'], 100) }}%">
                        @if ($quotaUsage['percentage'] > 10)
                            <span class="text-xs font-medium text-white">
                                {{ $quotaUsage['used_formatted'] }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="mt-1 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>0 MB</span>
                    <span>{{ $quotaUsage['quota_formatted'] }}</span>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Used</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ $quotaUsage['used_formatted'] }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($quotaUsage['used_mb']) }} MB
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Available</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ $quotaUsage['available_formatted'] }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($quotaUsage['available_mb']) }} MB
                    </div>
                </div>
            </div>

            <!-- Warnings -->
            @if ($quotaUsage['is_critical'])
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Quota Critical</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                                The mailbox is almost full. Please delete old emails or increase the quota.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif ($quotaUsage['is_warning'])
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Quota Warning</h3>
                            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
                                The mailbox is getting full. Consider cleaning up old emails.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex gap-2">
                <button wire:click="showUpdate" type="button"
                    class="inline-flex items-center px-4 py-2 bg-purple-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-800 focus:bg-purple-800 active:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:ring-offset-2 transition">
                    Update Quota
                </button>
                <button wire:click="refresh" type="button"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-700 focus:ring-offset-2 transition">
                    <svg wire:loading.remove wire:target="refresh" class="h-4 w-4 mr-2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg wire:loading wire:target="refresh" class="animate-spin h-4 w-4 mr-2" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">No quota data available</p>
        </div>
    @endif

    <!-- Update Quota Modal -->
    @if ($showUpdateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div wire:click="closeModals" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:w-full sm:max-w-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Update Quota</h3>
                    <form wire:submit="updateQuota" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                New Quota (MB)
                            </label>
                            <input wire:model="newQuota" type="number" min="100" max="10240" step="100"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-purple-700 focus:border-purple-700 sm:text-sm">
                            @error('newQuota')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ number_format($newQuota / 1024, 2) }} GB
                            </p>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <button type="button" wire:click="closeModals"
                                class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-purple-700 border border-transparent rounded-md text-sm font-medium text-white hover:bg-purple-800 disabled:opacity-50">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

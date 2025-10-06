<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Authentication</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Configure DKIM, SPF, and DMARC for {{ $site->domain }}
        </p>
    </div>

    <div class="space-y-6">
        <!-- DKIM Section -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">DKIM (DomainKeys Identified Mail)
                    </h4>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email signing and verification</p>
                </div>
                @if ($dkimKey)
                    <x-badge color="green" text="Enabled" />
                @else
                    <x-badge color="gray" text="Not Setup" />
                @endif
            </div>

            @if ($dkimKey)
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            DNS Record (TXT)
                        </label>
                        <div class="relative">
                            <pre
                                class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-xs overflow-x-auto">{{ $dkimKey->dns_record }}</pre>
                            <button wire:click="copyToClipboard('{{ $dkimKey->dns_record }}')" type="button"
                                class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-3">
                        <p class="text-xs text-blue-700 dark:text-blue-300">
                            Add this TXT record to your DNS settings to enable DKIM email signing.
                        </p>
                    </div>
                </div>
            @else
                <button wire:click="showSetup" type="button" wire:loading.attr="disabled" wire:target="setupDkim"
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                    <span wire:loading.remove wire:target="setupDkim">Setup DKIM</span>
                    <span wire:loading wire:target="setupDkim">Setting up...</span>
                </button>
            @endif
        </div>

        <!-- SPF Section -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">SPF (Sender Policy Framework)</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Authorize mail servers</p>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        DNS Record (TXT)
                    </label>
                    <div class="relative">
                        <pre
                            class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-xs overflow-x-auto">{{ $spfRecord }}</pre>
                        <button wire:click="copyToClipboard('{{ $spfRecord }}')" type="button"
                            class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DMARC Section -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white">DMARC (Domain-based Message
                    Authentication)
                </h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email authentication policy</p>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        DNS Record (TXT for _dmarc subdomain)
                    </label>
                    <div class="relative">
                        <pre
                            class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-xs overflow-x-auto">{{ $dmarcRecord }}</pre>
                        <button wire:click="copyToClipboard('{{ $dmarcRecord }}')" type="button"
                            class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refresh Button -->
        <button wire:click="refresh" type="button"
            class="w-full inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <svg wire:loading.remove wire:target="refresh" class="h-4 w-4 mr-2" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg wire:loading wire:target="refresh" class="animate-spin h-4 w-4 mr-2" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            Refresh Records
        </button>
    </div>

    <!-- Setup DKIM Modal -->
    @if ($showSetupModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div wire:click="closeModals" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:w-full sm:max-w-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Setup DKIM</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        This will generate a DKIM keypair on the server and provide you with a DNS record to add to your
                        domain.
                    </p>
                    <div class="flex gap-2 justify-end">
                        <button wire:click="closeModals" type="button"
                            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button wire:click="setupDkim" type="button" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="setupDkim">Setup DKIM</span>
                            <span wire:loading wire:target="setupDkim">Setting up...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="space-y-4">
    {{-- DKIM Section --}}
    <x-card>
        <x-slot name="header">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">DKIM (DomainKeys Identified Mail)</span>
            @if ($dkimKey)
                <x-badge color="green" text="Enabled" />
            @else
                <x-badge color="gray" text="Not Setup" />
            @endif
        </x-slot>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Email signing and verification</p>

        @if ($dkimKey)
            <div class="space-y-3">
                <div>
                    <x-label value="DNS Record (TXT)" class="mb-1" />
                    <div class="relative">
                        <pre class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-xs overflow-x-auto">{{ $dkimKey->dns_record }}</pre>
                        <button wire:click="copyToClipboard('{{ $dkimKey->dns_record }}')" type="button"
                            class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Copy">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <x-alert type="info" message="Add this TXT record to your DNS settings to enable DKIM email signing." />
            </div>
        @else
            <x-primary-button wire:click="showSetup" wire:loading.attr="disabled" wire:target="setupDkim" class="w-full justify-center">
                <span wire:loading.remove wire:target="setupDkim">Setup DKIM</span>
                <span wire:loading wire:target="setupDkim" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Setting up...</span>
            </x-primary-button>
        @endif
    </x-card>

    {{-- SPF Section --}}
    <x-card>
        <x-slot name="header">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">SPF (Sender Policy Framework)</span>
        </x-slot>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Authorize mail servers</p>
        <div>
            <x-label value="DNS Record (TXT)" class="mb-1" />
            <div class="relative">
                <pre class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-xs overflow-x-auto">{{ $spfRecord }}</pre>
                <button wire:click="copyToClipboard('{{ $spfRecord }}')" type="button"
                    class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Copy">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </x-card>

    {{-- DMARC Section --}}
    <x-card>
        <x-slot name="header">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">DMARC (Domain-based Message Authentication)</span>
        </x-slot>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Email authentication policy</p>
        <div>
            <x-label value="DNS Record (TXT for _dmarc subdomain)" class="mb-1" />
            <div class="relative">
                <pre class="p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded text-xs overflow-x-auto">{{ $dmarcRecord }}</pre>
                <button wire:click="copyToClipboard('{{ $dmarcRecord }}')" type="button"
                    class="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Copy">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </x-card>

    {{-- Refresh Button --}}
    <x-secondary-button wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh" class="w-full justify-center">
        <svg wire:loading.remove wire:target="refresh" class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <x-wire-spinner size="sm" wire:loading wire:target="refresh" />
        <span wire:loading.remove wire:target="refresh">Refresh Records</span>
        <span wire:loading wire:target="refresh">Refreshing...</span>
    </x-secondary-button>

    {{-- Setup DKIM Modal --}}
    @if ($showSetupModal)
        <x-modal title="Setup DKIM">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                This will generate a DKIM keypair on the server and provide you with a DNS record to add to your domain.
            </p>

            <x-slot name="footer">
                <x-secondary-button wire:click="closeModals">Cancel</x-secondary-button>
                <x-primary-button wire:click="setupDkim" wire:loading.attr="disabled" wire:target="setupDkim">
                    <span wire:loading.remove wire:target="setupDkim">Setup DKIM</span>
                    <span wire:loading wire:target="setupDkim" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Setting up...</span>
                </x-primary-button>
            </x-slot>
        </x-modal>
    @endif
</div>

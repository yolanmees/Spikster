<div class="max-w-2xl mx-auto space-y-6">
    <x-flash-messages />

    <x-card>
        <x-slot name="header">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <span>Disable Two-Factor Authentication</span>
            </div>
        </x-slot>

        <x-alert type="warning" title="Warning!" message="Disabling 2FA will make your account less secure. Are you sure you want to continue?" />

        <div class="mt-5">
            @if (!$confirmDisable)
                <x-secondary-button wire:click="$set('confirmDisable', true)" class="border-yellow-400 text-yellow-700 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20">
                    Yes, Disable 2FA
                </x-secondary-button>
            @else
                <div class="space-y-4">
                    <div>
                        <x-label for="verificationCode" value="Confirm with 2FA code or backup code" />
                        <x-input wire:model="verificationCode" type="text" id="verificationCode" placeholder="000000 or ABCD-1234"
                            class="mt-1 block w-full font-mono" />
                        @error('verificationCode') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3">
                        <x-danger-button wire:click="disable2FA" wire:loading.attr="disabled" wire:target="disable2FA">
                            <span wire:loading.remove wire:target="disable2FA">Disable 2FA</span>
                            <span wire:loading wire:target="disable2FA" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Disabling...</span>
                        </x-danger-button>
                        <x-secondary-button wire:click="$set('confirmDisable', false)">Cancel</x-secondary-button>
                    </div>
                </div>
            @endif
        </div>
    </x-card>
</div>

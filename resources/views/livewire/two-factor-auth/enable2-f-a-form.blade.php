<div class="max-w-2xl mx-auto space-y-6">
    <x-flash-messages />

    <x-card>
        <x-slot name="header">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span>Enable Two-Factor Authentication</span>
            </div>
        </x-slot>

        @if ($step === 1)
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Two-factor authentication adds an extra layer of security to your account.
                    You'll need an authenticator app like Google Authenticator, Authy, or 1Password.
                </p>
                <x-primary-button wire:click="generateSecret" wire:loading.attr="disabled" wire:target="generateSecret">
                    <span wire:loading.remove wire:target="generateSecret">Get Started</span>
                    <span wire:loading wire:target="generateSecret" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Loading...</span>
                </x-primary-button>
            </div>
        @endif

        @if ($step === 2)
            <div class="space-y-6">
                {{-- Step 1: QR Code --}}
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Step 1: Scan QR Code</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Scan this QR code with your authenticator app:</p>
                    <div class="flex justify-center p-6 bg-white rounded-lg border border-gray-200 dark:border-gray-700 mb-4">
                        {!! $qrCode !!}
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Or enter this code manually:</p>
                    <code class="block p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-mono text-gray-900 dark:text-white">
                        {{ $secret }}
                    </code>
                </div>

                {{-- Step 2: Verify --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Step 2: Verify Code</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Enter the 6-digit code from your authenticator app:</p>
                    <x-input wire:model="verificationCode" type="text" placeholder="000000" maxlength="6"
                        class="block w-full text-center text-2xl font-mono tracking-widest" />
                    @error('verificationCode') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Recovery Email --}}
                <div>
                    <x-label for="recoveryEmail" value="Recovery Email (Optional)" />
                    <x-input wire:model="recoveryEmail" type="email" id="recoveryEmail" placeholder="recovery@example.com" class="mt-1 block w-full" />
                    @error('recoveryEmail') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <x-primary-button wire:click="enable2FA" wire:loading.attr="disabled" wire:target="enable2FA" class="bg-green-600 hover:bg-green-700 focus:ring-green-500">
                    <span wire:loading.remove wire:target="enable2FA">Enable 2FA</span>
                    <span wire:loading wire:target="enable2FA" class="inline-flex items-center gap-1.5"><x-wire-spinner size="sm" /> Enabling...</span>
                </x-primary-button>
            </div>
        @endif

        @if ($step === 3 && $showBackupCodes)
            <div class="space-y-4">
                <x-alert type="warning" title="Important: Save these backup codes!"
                    message="Each code can only be used once. Store them in a safe place." />

                <div class="grid grid-cols-2 gap-2 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                    @foreach ($backupCodes as $code)
                        <code class="block p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded text-sm font-mono text-center text-gray-900 dark:text-white">
                            {{ $code }}
                        </code>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <x-secondary-button wire:click="downloadBackupCodes" wire:loading.attr="disabled" wire:target="downloadBackupCodes">
                        <span wire:loading.remove wire:target="downloadBackupCodes">Download Codes</span>
                        <span wire:loading wire:target="downloadBackupCodes"><x-wire-spinner size="sm" /></span>
                    </x-secondary-button>
                    <x-primary-button wire:click="finish" wire:loading.attr="disabled" wire:target="finish" class="bg-green-600 hover:bg-green-700 focus:ring-green-500">
                        <span wire:loading.remove wire:target="finish">Finish Setup</span>
                        <span wire:loading wire:target="finish"><x-wire-spinner size="sm" /></span>
                    </x-primary-button>
                </div>
            </div>
        @endif
    </x-card>
</div>

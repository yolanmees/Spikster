<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Disable Two-Factor Authentication</h2>

        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700">
            <p class="font-bold">Warning!</p>
            <p>Disabling 2FA will make your account less secure. Are you sure you want to continue?</p>
        </div>

        @if (!$confirmDisable)
            <button wire:click="$set('confirmDisable', true)" 
                    class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition">
                Yes, Disable 2FA
            </button>
        @else
            <div class="mb-4">
                <p class="mb-2 text-gray-700 dark:text-gray-300">
                    Enter your 2FA code or backup code to confirm:
                </p>
                <input type="text" 
                       wire:model="verificationCode" 
                       placeholder="000000 or ABCD-1234"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                @error('verificationCode')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex gap-2">
                <button wire:click="disable2FA" 
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                    Disable 2FA
                </button>
                <button wire:click="$set('confirmDisable', false)" 
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                    Cancel
                </button>
            </div>
        @endif
    </div>
</div>

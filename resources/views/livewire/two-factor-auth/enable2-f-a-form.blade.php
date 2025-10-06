<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Enable Two-Factor Authentication</h2>

        @if (session()->has('message'))
            <div class="mb-4 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($step === 1)
            <div class="mb-6">
                <p class="mb-4 text-gray-700 dark:text-gray-300">
                    Two-factor authentication adds an extra layer of security to your account. 
                    You'll need an authenticator app like Google Authenticator, Authy, or 1Password.
                </p>
                <button wire:click="generateSecret" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Get Started
                </button>
            </div>
        @endif

        @if ($step === 2)
            <div class="mb-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Step 1: Scan QR Code</h3>
                    <p class="mb-4 text-gray-700 dark:text-gray-300">
                        Scan this QR code with your authenticator app:
                    </p>
                    <div class="flex justify-center mb-4 p-4 bg-white rounded">
                        {!! $qrCode !!}
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Or enter this code manually:
                        </p>
                        <code class="block p-2 bg-gray-100 dark:bg-gray-700 rounded text-sm font-mono">
                            {{ $secret }}
                        </code>
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Step 2: Verify Code</h3>
                    <p class="mb-2 text-gray-700 dark:text-gray-300">
                        Enter the 6-digit code from your authenticator app:
                    </p>
                    <input type="text" 
                           wire:model="verificationCode" 
                           placeholder="000000"
                           maxlength="6"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    @error('verificationCode')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-gray-700 dark:text-gray-300">
                        Recovery Email (Optional)
                    </label>
                    <input type="email" 
                           wire:model="recoveryEmail" 
                           placeholder="recovery@example.com"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    @error('recoveryEmail')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <button wire:click="enable2FA" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                    Enable 2FA
                </button>
            </div>
        @endif

        @if ($step === 3 && $showBackupCodes)
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Backup Codes</h3>
                <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700">
                    <p class="font-bold">Important: Save these backup codes!</p>
                    <p>Each code can only be used once. Store them in a safe place.</p>
                </div>

                <div class="mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($backupCodes as $code)
                            <code class="block p-2 bg-white dark:bg-gray-600 rounded text-sm font-mono">
                                {{ $code }}
                            </code>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2">
                    <button wire:click="downloadBackupCodes" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Download Codes
                    </button>
                    <button wire:click="finish" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        Finish
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

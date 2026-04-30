<x-guest-layout x-data="{ recovery: false }">
    <h2 class="text-2xl font-bold text-white text-center mb-1">Two-Factor Authentication</h2>

    <p class="text-sm text-gray-400 text-center mb-8"
       x-show="!recovery">Enter the code from your authenticator app.</p>
    <p class="text-sm text-gray-400 text-center mb-8"
       x-show="recovery" x-cloak>Enter one of your emergency recovery codes.</p>

    <x-validation-errors class="mb-5" />

    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
        @csrf

        <div x-show="!recovery">
            <label for="code" class="block text-sm font-medium text-gray-300 mb-1.5">Authentication Code</label>
            <input id="code" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500 text-sm transition-colors tracking-widest text-center" />
        </div>

        <div x-show="recovery" x-cloak>
            <label for="recovery_code" class="block text-sm font-medium text-gray-300 mb-1.5">Recovery Code</label>
            <input id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500 text-sm transition-colors font-mono" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <button type="button"
                class="text-sm text-blue-400 hover:text-blue-300 transition-colors"
                x-show="!recovery"
                @click="recovery = true; $nextTick(() => $refs.recovery_code.focus())">
                Use a recovery code
            </button>
            <button type="button"
                class="text-sm text-blue-400 hover:text-blue-300 transition-colors"
                x-show="recovery" x-cloak
                @click="recovery = false; $nextTick(() => $refs.code.focus())">
                Use an authentication code
            </button>

            <button type="submit"
                class="py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/20 transition-all duration-200 text-sm">
                Log in
            </button>
        </div>
    </form>
</x-guest-layout>

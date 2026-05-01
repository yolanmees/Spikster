<x-guest-layout x-data="{ recovery: false }">
    <h2 class="text-2xl font-bold text-white text-center mb-1">Two-Factor Authentication</h2>

    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center mb-8"
       x-show="!recovery">Enter the code from your authenticator app.</p>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center mb-8"
       x-show="recovery" x-cloak>Enter one of your emergency recovery codes.</p>

    <x-validation-errors class="mb-5" />

    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
        @csrf

        <div x-show="!recovery">
            <label for="code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Authentication Code</label>
            <input id="code" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code"
                class="w-full px-4 py-2.5 bg-white border border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent placeholder-zinc-400 text-sm transition-colors tracking-widest text-center" />
        </div>

        <div x-show="recovery" x-cloak>
            <label for="recovery_code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Recovery Code</label>
            <input id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code"
                class="w-full px-4 py-2.5 bg-white border border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent placeholder-zinc-400 text-sm transition-colors font-mono" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <button type="button"
                class="text-sm text-purple-700 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition-colors"
                x-show="!recovery"
                @click="recovery = true; $nextTick(() => $refs.recovery_code.focus())">
                Use a recovery code
            </button>
            <button type="button"
                class="text-sm text-purple-700 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition-colors"
                x-show="recovery" x-cloak
                @click="recovery = false; $nextTick(() => $refs.code.focus())">
                Use an authentication code
            </button>

            <button type="submit"
                class="py-2.5 px-6 bg-gradient-to-r bg-zinc-950 hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-zinc-200 text-white font-semibold rounded-lg shadow-lg  transition-all duration-200 text-sm">
                Log in
            </button>
        </div>
    </form>
</x-guest-layout>

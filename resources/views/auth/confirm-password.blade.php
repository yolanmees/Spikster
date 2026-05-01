<x-guest-layout>
    <h2 class="text-2xl font-bold text-white text-center mb-1">Confirm Password</h2>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center mb-8">This is a secure area. Please confirm your password before continuing.</p>

    <x-validation-errors class="mb-5" />

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" autofocus
                class="w-full px-4 py-2.5 bg-white border border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent placeholder-zinc-400 text-sm transition-colors" />
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="py-2.5 px-6 bg-gradient-to-r bg-zinc-950 hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-zinc-200 text-white font-semibold rounded-lg shadow-lg  transition-all duration-200 text-sm">
                Confirm
            </button>
        </div>
    </form>
</x-guest-layout>

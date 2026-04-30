<x-guest-layout>
    <h2 class="text-2xl font-bold text-white text-center mb-1">Confirm Password</h2>
    <p class="text-sm text-gray-400 text-center mb-8">This is a secure area. Please confirm your password before continuing.</p>

    <x-validation-errors class="mb-5" />

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" autofocus
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500 text-sm transition-colors" />
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/20 transition-all duration-200 text-sm">
                Confirm
            </button>
        </div>
    </form>
</x-guest-layout>

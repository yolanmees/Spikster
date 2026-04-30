<x-guest-layout>
    <h2 class="text-2xl font-bold text-white text-center mb-1">Reset password</h2>
    <p class="text-sm text-gray-400 text-center mb-8">Choose a new secure password.</p>

    <x-validation-errors class="mb-5" />

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors" />
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors" />
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors" />
        </div>
        <button type="submit"
            class="w-full py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/20 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] text-sm">
            Reset Password
        </button>
    </form>
</x-guest-layout>

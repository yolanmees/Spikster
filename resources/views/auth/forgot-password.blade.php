<x-guest-layout>
    <h2 class="text-2xl font-bold text-white text-center mb-1">Forgot password?</h2>
    <p class="text-sm text-gray-400 text-center mb-8">Enter your email and we'll send a reset link.</p>

    @if (session('status'))
        <x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>
    @endif

    <x-validation-errors class="mb-5" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors" />
        </div>
        <button type="submit"
            class="w-full py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/20 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] text-sm">
            Send Reset Link
        </button>
    </form>
    <p class="text-center text-sm text-gray-500 mt-6">
        <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 transition-colors">Back to sign in</a>
    </p>
</x-guest-layout>

<x-guest-layout>
    <h2 class="text-2xl font-bold text-white text-center mb-1">Welcome back</h2>
    <p class="text-sm text-gray-400 text-center mb-8">Sign in to your account</p>

    <x-validation-errors class="mb-5" />

    @if (session('status'))
        <x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500 text-sm transition-colors" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500 text-sm transition-colors" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-gray-600 bg-gray-800 text-blue-500 focus:ring-blue-500" />
                <span class="text-sm text-gray-400">Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit"
            class="w-full py-2.5 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/20 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] text-sm">
            Sign in
        </button>
    </form>
</x-guest-layout>

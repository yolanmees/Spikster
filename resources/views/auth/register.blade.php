<x-guest-layout>
    <h2 class="text-2xl font-bold text-white text-center mb-1">Create an account</h2>
    <p class="text-sm text-gray-400 text-center mb-8">Fill in your details to get started</p>

    <x-validation-errors class="mb-5" />

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-300 mb-1.5">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent placeholder-gray-500 text-sm transition-colors" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent placeholder-gray-500 text-sm transition-colors" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent placeholder-gray-500 text-sm transition-colors" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1.5">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-700 focus:border-transparent placeholder-gray-500 text-sm transition-colors" />
        </div>

        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
            <div class="flex items-start gap-2">
                <input id="terms" type="checkbox" name="terms"
                    class="mt-0.5 rounded border-gray-600 bg-gray-800 text-purple-700 focus:ring-purple-700" />
                <label for="terms" class="text-sm text-gray-400">
                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-purple-400 hover:text-purple-300 underline">'.__('Terms of Service').'</a>',
                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-purple-400 hover:text-purple-300 underline">'.__('Privacy Policy').'</a>',
                    ]) !!}
                </label>
            </div>
        @endif

        <div class="flex items-center justify-between pt-1">
            <a href="{{ route('login') }}" class="text-sm text-purple-400 hover:text-purple-300 transition-colors">
                Already registered?
            </a>
            <button type="submit"
                class="py-2.5 px-6 bg-gradient-to-r from-purple-700 to-purple-800 hover:from-purple-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg shadow-purple-500/20 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] text-sm">
                Register
            </button>
        </div>
    </form>
</x-guest-layout>

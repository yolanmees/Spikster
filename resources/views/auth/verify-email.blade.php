<x-guest-layout>
    <h2 class="text-2xl font-bold text-white text-center mb-1">Verify your email</h2>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center mb-8">
        Before continuing, please verify your email by clicking the link we sent you.
        Didn't receive it? We'll resend it.
    </p>

    @if (session('status') == 'verification-link-sent')
        <x-alert type="success" class="mb-5">
            A new verification link has been sent to your email address.
        </x-alert>
    @endif

    <div class="flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                class="w-full py-2.5 px-6 bg-gradient-to-r bg-zinc-950 hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-zinc-200 text-white font-semibold rounded-lg shadow-lg  transition-all duration-200 text-sm">
                Resend Verification Email
            </button>
        </form>

        <div class="flex items-center justify-between text-sm">
            <a href="{{ route('profile.show') }}" class="text-purple-700 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition-colors">
                Edit Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:text-zinc-300 transition-colors">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>

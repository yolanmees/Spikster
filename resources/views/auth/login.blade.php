@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-zinc-950 dark:bg-white shadow-sm mb-4">
                <svg class="w-8 h-8 text-white dark:text-zinc-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-zinc-950 dark:text-white">{{ config('app.name') }}</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Sign in to your account</p>
        </div>

        {{-- Card --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-soft p-8">
            @if (session('status'))
                <x-alert type="info" class="mb-6">{{ session('status') }}</x-alert>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-label for="email" value="Email address" />
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        :value="old('email')"
                        placeholder="you@example.com"
                        required
                        autofocus
                        autocomplete="username"
                        class="!border-zinc-200 dark:!bg-zinc-800 dark:!border-zinc-700 dark:!text-white !placeholder-zinc-400 focus:!border-purple-700"
                    />
                    <x-input-error for="email" class="mt-1.5" />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <x-label for="password" value="Password" class="!mb-0" />
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs text-purple-700 dark:text-purple-400 hover:text-purple-800 transition-colors">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        class="!border-zinc-200 dark:!bg-zinc-800 dark:!border-zinc-700 dark:!text-white !placeholder-zinc-400 focus:!border-purple-700"
                    />
                    <x-input-error for="password" class="mt-1.5" />
                </div>

                <div class="flex items-center gap-2">
                    <x-checkbox id="remember_me" name="remember" />
                    <label for="remember_me" class="text-sm text-zinc-600 dark:text-zinc-400 cursor-pointer">Remember me</label>
                </div>

                <x-button type="submit" variant="primary" class="w-full justify-center py-2.5">
                    Sign in
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </x-button>
            </form>
        </div>

        <p class="text-center text-xs text-zinc-500 dark:text-zinc-400 mt-6">
            {{ config('app.name') }} · Server Management Panel
        </p>
    </div>
</div>
@endsection

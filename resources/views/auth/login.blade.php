@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg shadow-blue-500/25 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">{{ config('app.name') }}</h1>
            <p class="text-sm text-gray-400 mt-1">Sign in to your account</p>
        </div>

        {{-- Card --}}
        <div class="bg-gray-800/50 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-8">
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
                        class="!bg-gray-700/50 !border-gray-600 !text-white !placeholder-gray-400 focus:!border-blue-500"
                    />
                    <x-input-error for="email" class="mt-1.5" />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <x-label for="password" value="Password" class="!mb-0" />
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
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
                        class="!bg-gray-700/50 !border-gray-600 !text-white !placeholder-gray-400 focus:!border-blue-500"
                    />
                    <x-input-error for="password" class="mt-1.5" />
                </div>

                <div class="flex items-center gap-2">
                    <x-checkbox id="remember_me" name="remember" />
                    <label for="remember_me" class="text-sm text-gray-400 cursor-pointer">Remember me</label>
                </div>

                <x-button type="submit" variant="primary" class="w-full justify-center py-2.5">
                    Sign in
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </x-button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-500 mt-6">
            {{ config('app.name') }} · Server Management Panel
        </p>
    </div>
</div>
@endsection

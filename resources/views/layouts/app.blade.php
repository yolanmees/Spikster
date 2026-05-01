<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', v => localStorage.setItem('darkMode', v))"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('cipi.name', config('app.name')) }} · @yield('title')</title>
    <link rel="icon" type="image/png" href="/favicon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>[x-cloak]{display:none!important}</style>
    @yield('css')
    @stack('styles')

</head>

<body class="bg-gray-100 dark:bg-gray-950 antialiased">

    {{-- Mobile sidebar overlay --}}
    @include('layouts.components.mobile-sidebar')

    {{-- Desktop sidebar --}}
    @include('layouts.components.sidebar')

    {{-- Main content wrapper --}}
    <div class="xl:pl-72 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-white/10 bg-gray-900 dark:bg-gray-900 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
            {{-- Mobile hamburger --}}
            <button x-on:click="sidebarOpen = true" type="button"
                class="-m-2.5 p-2.5 text-white xl:hidden hover:text-gray-300 transition-colors"
                aria-label="Open sidebar">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10zm0 5.25a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div class="flex flex-1 items-center justify-end gap-x-3">
                {{-- Page title (injected by pages) --}}
                <div class="mr-auto hidden sm:block text-sm text-gray-400 font-medium">
                    @yield('topbar-title')
                </div>

                {{-- Dark mode toggle --}}
                <button @click="darkMode = !darkMode" type="button"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-all duration-200"
                    :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                {{-- User avatar dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" type="button"
                        class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-white/10 transition-all duration-200">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-white">{{ Auth::user()->name ?? '' }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black/10 dark:ring-white/10 py-1 z-50">
                        <a href="{{ route('profile.show') }}"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-4 pt-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
            @endif
            @if(session('error'))
                <x-alert type="error" :dismissible="true">{{ session('error') }}</x-alert>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        {{-- Extra slot --}}
        @hasSection('extra')
            <div class="px-4 sm:px-6 lg:px-8 pb-6">
                @yield('extra')
            </div>
        @endif
    </div>

    <script>
        @php
            use Firebase\JWT\JWT;
            $jwtSecret = config('cipi.jwt_secret');
            $jwtToken = JWT::encode(['iat' => time(), 'exp' => time() + 900], $jwtSecret . '-Acs', 'HS256');
        @endphp
        localStorage.setItem('access_token', '{{ $jwtToken }}');
    </script>
    @stack('scripts')
    @yield('js')
</body>
</html>

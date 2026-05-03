<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ sidebarOpen: false, darkMode: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
    x-init="
        $watch('darkMode', v => {
            localStorage.setItem('theme', v ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', v ? 'dark' : 'light');
        });
        document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
    "
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

    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('theme');
                var isDark = savedTheme
                    ? savedTheme === 'dark'
                    : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            } catch (e) {
                // Ignore theme bootstrap failures and continue rendering.
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script defer src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) { lucide.createIcons(); }
        });
    </script>

    <style>[x-cloak]{display:none!important}</style>
    @yield('css')
    @stack('styles')

</head>

<body class="bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-100">

    {{-- Mobile sidebar overlay --}}
    @include('layouts.components.mobile-sidebar')

    {{-- Desktop sidebar --}}
    @include('layouts.components.sidebar')

    {{-- Main content wrapper --}}
    <div class="xl:pl-72 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-zinc-200 bg-white/88 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/88 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
            {{-- Mobile hamburger --}}
            <button x-on:click="sidebarOpen = true" type="button"
                class="-m-2.5 p-2.5 text-zinc-500 xl:hidden hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors"
                aria-label="Open sidebar">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10zm0 5.25a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div class="flex flex-1 items-center justify-end gap-x-3">
                {{-- Page title (injected by pages) --}}
                <div class="mr-auto hidden sm:block text-sm text-zinc-500 dark:text-zinc-400 font-medium">
                    @yield('topbar-title')
                </div>

                {{-- Search button --}}
                <button type="button"
                    class="hidden h-9 min-w-56 items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 text-left text-sm text-zinc-500 shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 md:flex transition-colors">
                    <i data-lucide="search" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Zoeken...</span>
                </button>
                <button type="button"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-white text-zinc-600 shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors md:hidden"
                    aria-label="Zoeken">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>

                {{-- Dark mode toggle --}}
                <button @click="darkMode = !darkMode" type="button"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-white text-zinc-600 shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors"
                    :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                    <i x-show="!darkMode" data-lucide="moon" class="w-4 h-4"></i>
                    <i x-show="darkMode" x-cloak data-lucide="sun" class="w-4 h-4"></i>
                </button>

                {{-- Notifications --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" type="button"
                        class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg border border-zinc-200 bg-white text-zinc-600 shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors"
                        aria-label="Notificaties">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                    </button>
                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900 z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Notifications</p>
                        </div>
                        <div class="px-4 py-8 text-center">
                            <i data-lucide="bell-off" class="w-8 h-8 mx-auto text-zinc-300 dark:text-zinc-600 mb-2"></i>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No notifications</p>
                        </div>
                    </div>
                </div>

                {{-- User avatar dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" type="button"
                        class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all duration-200">
                        <div class="h-8 w-8 rounded-full bg-zinc-900 dark:bg-white flex items-center justify-center text-white dark:text-zinc-950 font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ Auth::user()->name ?? '' }}</span>
                        <svg class="w-4 h-4 text-zinc-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white dark:bg-zinc-900 shadow-xl ring-1 ring-zinc-200 dark:ring-zinc-700 py-1 z-50">
                        <a href="{{ route('profile.show') }}" wire:navigate
                            class="flex items-center gap-2 px-4 py-2.5 text-sm text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <div class="my-1 border-t border-zinc-100 dark:border-zinc-700"></div>
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

    @auth
    <script>
        @php
            try {
                // Revoke any existing 'dashboard' token for this user, then create a fresh one.
                // The plain-text token is only visible once — it is stored in localStorage for API calls.
                Auth::user()->tokens()->where('name', 'dashboard')->delete();
                $sanctumToken = Auth::user()->createToken('dashboard', ['*'])->plainTextToken;
            } catch (\Throwable $e) {
                $sanctumToken = '';
            }
        @endphp
        @if($sanctumToken)
        localStorage.setItem('sanctum_token', '{{ $sanctumToken }}');
        @endif
    </script>
    @endauth
    <script>
        // Global API helper — wraps fetch() with automatic Sanctum auth header.
        // Usage: api('/api/servers') → GET with JSON
        //        api('/api/sites/1', 'POST', { domain: 'example.com' }) → POST with JSON body
        async function api(url, method = 'GET', data = null) {
            const token = localStorage.getItem('sanctum_token') || '';
            const opts = {
                method,
                headers: {
                    'Authorization': token ? ('Bearer ' + token) : '',
                    'Accept': 'application/json',
                },
            };
            if (data) {
                opts.headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(data);
            }
            const res = await fetch(url, opts);
            if (res.status === 401) {
                localStorage.removeItem('sanctum_token');
                window.location.replace('/login');
                throw new Error('Unauthorized — redirecting to login');
            }
            if (!res.ok) {
                const text = await res.text();
                throw new Error(text || res.statusText);
            }
            return res.json();
        }
    </script>
    @stack('scripts')
    @livewireScripts
    @yield('js')
</body>
</html>

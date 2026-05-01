<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('cipi.name', config('app.name')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            var t = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
            if (t === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-6 py-12">
        {{-- Logo --}}
        <div class="flex justify-center mb-8">
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-950 dark:bg-white flex items-center justify-center shadow-sm">
                    <svg class="w-6 h-6 text-white dark:text-zinc-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-zinc-950 dark:text-white">{{ config('cipi.name', config('app.name')) }}</span>
            </a>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-soft p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('cipi.name', config('app.name')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center antialiased">
    <div class="w-full max-w-md px-6 py-12">
        {{-- Logo --}}
        <div class="flex justify-center mb-8">
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-white">{{ config('cipi.name', config('app.name')) }}</span>
            </a>
        </div>

        <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>

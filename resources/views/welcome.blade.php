<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex">
    <title>{{ config('cipi.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center antialiased">
    <div class="relative w-full max-w-md px-6 text-center">
        {{-- Logo mark --}}
        <div class="flex justify-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-2xl shadow-blue-500/30">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>

        <h1 class="text-4xl font-bold text-white mb-2 tracking-tight">{{ config('cipi.name') }}</h1>
        <p class="text-gray-400 text-sm mb-10">Server management, simplified.</p>

        <a href="/dashboard"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/20 transition-all duration-200 transform hover:scale-105 active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            Go to Dashboard
        </a>

        <div class="flex justify-center gap-8 mt-10">
            @if(config('cipi.website'))
                <a href="{{ config('cipi.website') }}" target="_blank"
                    class="text-xs font-medium text-gray-500 hover:text-gray-300 uppercase tracking-widest transition-colors">Website</a>
            @endif
            @if(config('cipi.documentation'))
                <a href="{{ config('cipi.documentation') }}" target="_blank"
                    class="text-xs font-medium text-gray-500 hover:text-gray-300 uppercase tracking-widest transition-colors">Docs</a>
            @endif
            @if(config('cipi.app'))
                <a href="{{ config('cipi.app') }}" target="_blank"
                    class="text-xs font-medium text-gray-500 hover:text-gray-300 uppercase tracking-widest transition-colors">Mobile App</a>
            @endif
        </div>
    </div>
</body>
</html>

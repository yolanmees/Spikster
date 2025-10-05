<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex">
    <title>{{ config('cipi.name') }} | {{ __('spikster.error') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <img class="mb-8 w-64 mx-auto" src="/assets/img/error.png" alt="500" />
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ __('spikster.error') }} 500</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">{{ __('spikster.internal_server_error') }}</p>
            <a href="/"
                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('spikster.return_to_dashboard') }}
            </a>
        </div>
    </div>
</body>

</html>

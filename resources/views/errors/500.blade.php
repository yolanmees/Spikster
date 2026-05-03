@extends('layouts.guest')

@section('title', '500 — Server Error')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-red-50 dark:bg-red-900/20 mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-7xl font-black text-gray-900 dark:text-white mb-2">500</h1>
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-3">Server Error</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Something went wrong on our end. Please try again later.</p>
        <a href="/dashboard"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-700 hover:bg-purple-800 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>
@endsection

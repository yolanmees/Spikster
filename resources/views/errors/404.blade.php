@extends('layouts.guest')

@section('title', '404 — Not Found')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-50 dark:bg-blue-900/20 mb-6">
            <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-7xl font-black text-gray-900 dark:text-white mb-2">404</h1>
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-3">Page Not Found</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">The page you're looking for doesn't exist or has been moved.</p>
        <a href="/dashboard"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>
@endsection

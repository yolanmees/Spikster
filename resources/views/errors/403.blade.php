@extends('layouts.guest')

@section('title', '403 — Forbidden')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-yellow-50 dark:bg-yellow-900/20 mb-6">
            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-7xl font-black text-gray-900 dark:text-white mb-2">403</h1>
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-3">Access Forbidden</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">You don't have permission to access this resource.</p>
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

@extends('layouts.app')

@section('title')
    {{ __('spikster.server_crontab') }}
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('server.edit', $server_id) }}"
                class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Server
            </a>
        </div>

        @livewire('server.cron-manager', ['server' => \App\Models\Server::where('server_id', $server_id)->firstOrFail()])
    </div>
@endsection

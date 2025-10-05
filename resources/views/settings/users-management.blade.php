@extends('layouts.settings')

@section('title')
    {{ __('spikster.titles.settings') }} - Users
@endsection

@section('settings-content')
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Manage users and their access</p>
            </div>
        </div>
    </div>

    <livewire:settings.user-management />
@endsection

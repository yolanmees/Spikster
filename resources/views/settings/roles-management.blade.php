@extends('layouts.settings')

@section('title')
    {{ __('spikster.titles.settings') }} - Roles
@endsection

@section('settings-content')
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Role Management</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Create and manage roles with permissions</p>
            </div>
        </div>
    </div>

    <livewire:settings.role-management />
@endsection

@extends('layouts.app')

@section('title', $module->name . ' - Module Details')

@section('content')
    {{-- Header with Back Button --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $module->name }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Module Details</p>
        </div>
        <a href="{{ route('modules.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Modules
        </a>
    </div>
    {{-- Module Info Card --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        @if ($module->is_active)
                            <div
                                class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        @else
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $module->name }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $module->description }}</p>

                        <div class="flex items-center space-x-4 mt-4">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $module->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $module->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if ($module->version)
                                <span class="text-sm text-gray-500 dark:text-gray-400">Version
                                    {{ $module->version }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <form method="POST" action="{{ route('modules.toggle', $module->id) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $module->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ $module->is_active ? 'Disable Module' : 'Enable Module' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Module Details --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Menu Items --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                        </path>
                    </svg>
                    Menu Items
                </h4>

                @if ($module->menuItems->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No menu items registered</p>
                @else
                    <div class="space-y-2">
                        @foreach ($module->menuItems as $menuItem)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    @if ($menuItem->icon)
                                        <div class="text-gray-600 dark:text-gray-300">
                                            {!! $menuItem->icon !!}
                                        </div>
                                    @endif
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $menuItem->title }}</span>
                                </div>
                                <span
                                    class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                    {{ $menuItem->menu_location }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Permissions --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    Permissions
                </h4>

                @if ($module->permissions->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No permissions registered</p>
                @else
                    <div class="space-y-2">
                        @foreach ($module->permissions as $permission)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $permission->name }}</div>
                                @if ($permission->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $permission->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Module Metadata --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Module Information</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Slug</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $module->slug }}
                    </dd>
                </div>

                @if ($module->version)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Version</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $module->version }}</dd>
                    </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $module->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $module->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Registered At</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $module->created_at->format('M d, Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $module->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

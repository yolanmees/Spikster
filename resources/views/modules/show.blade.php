@extends('layouts.app')

@section('title', $module->name . ' — Module Details')

@section('content')
    <div class="space-y-6">

        <x-page-header :title="$module->name" subtitle="Module Details">
            <x-slot name="actions">
                <x-back-button :href="route('modules.index')" label="Back to Modules" />
            </x-slot>
        </x-page-header>

        {{-- Module Header Card --}}
        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-16 h-16 rounded-xl flex items-center justify-center {{ $module->is_active ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-100 dark:bg-gray-700' }}">
                        @if ($module->is_active)
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $module->name }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">{{ $module->description }}</p>
                        <div class="flex items-center gap-3 mt-3">
                            <x-badge :color="$module->is_active ? 'green' : 'gray'" :text="$module->is_active ? 'Active' : 'Inactive'" />
                            @if ($module->version)
                                <span class="text-sm text-gray-500 dark:text-gray-400">v{{ $module->version }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('modules.toggle', $module->id) }}" class="shrink-0">
                    @csrf
                    @if ($module->is_active)
                        <x-danger-button type="submit">Disable Module</x-danger-button>
                    @else
                        <x-primary-button type="submit">Enable Module</x-primary-button>
                    @endif
                </form>
            </div>
        </x-card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Menu Items --}}
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Menu Items
                    </div>
                </x-slot>
                @if ($module->menuItems->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No menu items registered</p>
                @else
                    <div class="space-y-2">
                        @foreach ($module->menuItems as $item)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    @if ($item->icon)
                                        <div class="text-gray-500 dark:text-gray-300">{!! $item->icon !!}</div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->title }}</span>
                                </div>
                                <x-badge color="blue" :text="$item->menu_location" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Permissions --}}
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Permissions
                    </div>
                </x-slot>
                @if ($module->permissions->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No permissions registered</p>
                @else
                    <div class="space-y-2">
                        @foreach ($module->permissions as $permission)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $permission->name }}</div>
                                @if ($permission->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $permission->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Module Metadata --}}
        <x-card>
            <x-slot name="header">Module Information</x-slot>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Slug</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-white">{{ $module->slug }}</dd>
                </div>
                @if ($module->version)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Version</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $module->version }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</dt>
                    <dd class="mt-1"><x-badge :color="$module->is_active ? 'green' : 'gray'" :text="$module->is_active ? 'Active' : 'Inactive'" /></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Registered At</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $module->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $module->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </x-card>

    </div>
@endsection

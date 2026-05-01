@extends('layouts.app')

@section('title', 'Module Manager')

@section('content')
    <div class="space-y-6">
        <x-page-header />

        <x-flash-messages />

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-stat-card title="Total Modules" :value="$installed" color="blue"
                icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>' />
            <x-stat-card title="Active Modules" :value="$active" color="green"
                icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' />
            <x-stat-card title="Inactive Modules" :value="$installed - $active" color="gray"
                icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>' />
        </div>

        {{-- Modules List --}}
        <x-card>
            <x-slot name="header">Installed Modules</x-slot>

            @if ($modules->isEmpty())
                <x-empty-state icon="module" title="No modules installed" message="Get started by installing your first module." />
            @else
                <div class="space-y-3">
                    @foreach ($modules as $module)
                        <div class="flex items-center gap-4 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-500/50 transition-all duration-200">
                            {{-- Status Icon --}}
                            <div class="shrink-0 w-12 h-12 rounded-lg flex items-center justify-center {{ $module->is_active ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-100 dark:bg-gray-700' }}">
                                @if ($module->is_active)
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $module->name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $module->description }}</p>
                                <div class="flex items-center gap-3 mt-1.5">
                                    <x-badge :color="$module->is_active ? 'green' : 'gray'" :text="$module->is_active ? 'Active' : 'Inactive'" />
                                    @if ($module->version)
                                        <span class="text-xs text-gray-400">v{{ $module->version }}</span>
                                    @endif
                                    @if ($module->menuItems->isNotEmpty())
                                        <span class="text-xs text-gray-400">{{ $module->menuItems->count() }} menu {{ Str::plural('item', $module->menuItems->count()) }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-3 shrink-0">
                                <form method="POST" action="{{ route('modules.toggle', $module->id) }}">
                                    @csrf
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" {{ $module->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </form>
                                <a href="{{ route('modules.show', $module->id) }}"
                                   class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                   title="View details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
@endsection

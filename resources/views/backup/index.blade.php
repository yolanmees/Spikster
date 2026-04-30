@extends('layouts.app')

@section('title', 'Backup Management')

@section('content')
    <div class="space-y-6" x-data="{ activeTab: 'backups' }">
        <x-page-header title="Backup Management">
            <x-slot name="subtitle">{{ $site->domain }}</x-slot>
            <x-slot name="actions">
                <a href="{{ route('site.edit', $site->site_id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Site
                </a>
            </x-slot>
        </x-page-header>

        <x-alpine-tabs model="activeTab" :tabs="[
            ['key' => 'backups', 'label' => 'Backups', 'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4\'/></svg>'],
            ['key' => 'schedules', 'label' => 'Schedules', 'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'],
            ['key' => 'storage', 'label' => 'Storage Locations', 'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4\'/></svg>'],
        ]" />

        <div x-show="activeTab === 'backups'" x-cloak>
            @livewire('backup.backups-table', ['site' => $site])
        </div>
        <div x-show="activeTab === 'schedules'" x-cloak>
            @livewire('backup.backup-schedule-manager', ['site' => $site])
        </div>
        <div x-show="activeTab === 'storage'" x-cloak>
            @livewire('backup.storage-location-manager')
        </div>
    </div>
@endsection

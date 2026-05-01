@extends('layouts.app')

@section('title')
    {{ __('spikster.titles.settings') }}
@endsection

@section('content')
    @php
        $sections = [
            'general' => 'General',
            'users'   => 'Users',
            'roles'   => 'Roles & Permissions',
        ];

        $sectionIcons = [
            'general' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'users'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
            'roles'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        ];

        $navItems = collect($sections)->map(fn($label, $key) => [
            'href'   => route('settings.section', ['section' => $key]),
            'label'  => $label,
            'icon'   => $sectionIcons[$key] ?? null,
            'active' => $section === $key,
        ])->values()->all();
    @endphp

    <x-section-nav :items="$navItems" title="Settings">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-3">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ $sections[$section] ?? 'General' }}</h2>
        </div>

        @includeIf('settings.manage.sections.' . $section)
    </x-section-nav>
@endsection

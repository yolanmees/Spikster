@extends('layouts.app')



@section('title')
    {{ __('spikster.titles.settings') }}
@endsection



@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

        <div class="col-span-1 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800">
            <h5 class="font-bold text-gray-900 dark:text-gray-300 px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                {{ __('spikster.titles.settings') }}</h5>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item">
                    <a href="{{ route('settings.general') }}"
                        class="flex items-center gap-2 text-decoration-none hover:bg-gray-100 dark:hover:bg-gray-900 text-sm px-4 py-2 transition-colors @if (request()->routeIs('settings.general')) font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 @else text-gray-700 dark:text-gray-300 @endif">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        General Settings
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('settings.users') }}"
                        class="flex items-center gap-2 text-decoration-none hover:bg-gray-100 dark:hover:bg-gray-900 text-sm px-4 py-2 transition-colors @if (request()->routeIs('settings.users')) font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 @else text-gray-700 dark:text-gray-300 @endif">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Users
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('settings.roles') }}"
                        class="flex items-center gap-2 text-decoration-none hover:bg-gray-100 dark:hover:bg-gray-900 text-sm px-4 py-2 transition-colors @if (request()->routeIs('settings.roles')) font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 @else text-gray-700 dark:text-gray-300 @endif">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Roles & Permissions
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4">
            @yield('settings-content')
        </div>

    </div>
@endsection

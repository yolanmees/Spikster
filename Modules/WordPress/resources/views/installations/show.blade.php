@extends('layouts.app')

@section('title', 'WordPress Installation Details')

@section('content')
    <div x-data="{ tab: 'overview' }">
        <!-- Breadcrumb -->
        <ol class="breadcrumbs">
            <li class="breadcrumb-item"><a href="{{ route('wordpress.index') }}">WordPress</a></li>
            <li class="breadcrumb-item active">{{ $installation->site->domain }}</li>
            <li class="breadcrumb-item active">Version: <b><span class="ml-1">{{ $installation->version ?? 'Unknown' }}</span></b></li>
            <li class="breadcrumb-item active">Status: <b><span class="ml-1 {{ $installation->status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-gray-600' }}">{{ ucfirst($installation->status) }}</span></b></li>
        </ol>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="pb-6">
            <div class="sm:hidden">
                <label for="tabs" class="sr-only">Select a tab</label>
                <select id="tabs" name="tabs" x-model="tab"
                    class="block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                    <option value="overview">Overview</option>
                    <option value="themes">Themes</option>
                    <option value="plugins">Plugins</option>
                    <option value="updates">Updates</option>
                    <option value="settings">Settings</option>
                </select>
            </div>
            <div class="hidden sm:block">
                <div class="border-b-2 border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px space-x-6" aria-label="Tabs">
                        <button @click="tab = 'overview'"
                            :class="tab === 'overview' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'overview' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Overview
                        </button>
                        <button @click="tab = 'themes'"
                            :class="tab === 'themes' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'themes' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            Themes ({{ $installation->themes->count() }})
                        </button>
                        <button @click="tab = 'plugins'"
                            :class="tab === 'plugins' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'plugins' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Plugins ({{ $installation->plugins->count() }})
                        </button>
                        <button @click="tab = 'updates'"
                            :class="tab === 'updates' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'updates' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Updates
                            @if($installation->hasUpdatesAvailable())
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                    {{ $installation->getUpdatesCount() }}
                                </span>
                            @endif
                        </button>
                        <button @click="tab = 'settings'"
                            :class="tab === 'settings' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'settings' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Overview Tab -->
        <div x-show="tab === 'overview'" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Quick Stats Card -->
                <x-card header="WordPress Information" size="md" dark="false">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.158 12.786l-2.698 7.84c.942.32 1.948.492 2.998.492 1.188 0 2.335-.218 3.396-.617-.028-.044-.054-.09-.076-.137l-3.62-7.578zm-5.408 2.01l-1.028-2.764c-.187-.476-.25-.857-.25-1.196 0-1.24.949-2.396 2.05-2.396.107 0 .208.014.308.028.065.014.13.028.19.028-.73-.681-1.702-1.1-2.776-1.1-2.206 0-4.244 1.676-4.244 4.236 0 .842.27 1.625.728 2.264l2.022 5.9zm9.656-9.47c0-.745-.268-1.26-.498-1.66-.306-.498-.594-.92-.594-1.418 0-.556.425-1.073 1.023-1.073.027 0 .052.003.078.006C15.022.554 13.558 0 12 0 9.803 0 7.87 1.158 6.699 2.904c.188.006.364.01.514.01 1.036 0 2.634-.126 2.634-.126.533-.03.595.752.062.814 0 0-.536.062-1.132.094l3.6 10.7 2.162-6.486-1.537-4.214c-.533-.032-1.038-.094-1.038-.094-.532-.03-.47-.844.063-.814 0 0 1.625.126 2.594.126.836 0 2.133-.126 2.133-.126.533-.03.595.752.062.814 0 0-.537.062-1.133.094l3.572 10.627 1.014-3.387c.438-1.404.772-2.41.772-3.28z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">WordPress Details</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Installation information</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Version</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $installation->version ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</span>
                                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full {{ $installation->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($installation->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Locale</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $installation->locale }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto Update</span>
                                <span class="text-sm font-semibold {{ $installation->auto_update ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                                    {{ $installation->auto_update ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Quick Actions Card -->
                <x-card header="Quick Actions" size="md" dark="false">
                    <div class="space-y-3">
                        <a href="{{ $installation->getAdminUrl() }}" target="_blank" class="block">
                            <button type="button" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-blue-500 dark:border-blue-600 text-sm font-semibold rounded-lg text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 focus:outline-none focus:ring-4 focus:ring-blue-300/50 transform hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Open WP Admin
                            </button>
                        </a>

                        <form action="{{ route('wordpress.check-updates', $installation->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-gray-300 dark:border-gray-600 text-sm font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300/50 transform hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Check for Updates
                            </button>
                        </form>

                        <form action="{{ route('wordpress.sync-themes', $installation->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-gray-300 dark:border-gray-600 text-sm font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300/50 transform hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Sync Themes
                            </button>
                        </form>

                        <form action="{{ route('wordpress.sync-plugins', $installation->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-gray-300 dark:border-gray-600 text-sm font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300/50 transform hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Sync Plugins
                            </button>
                        </form>

                        <form action="{{ route('wordpress.destroy', $installation->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will delete all WordPress files and database.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-red-500 dark:border-red-600 text-sm font-semibold rounded-lg text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 focus:outline-none focus:ring-4 focus:ring-red-300/50 transform hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Uninstall WordPress
                            </button>
                        </form>
                    </div>
                </x-card>

                <!-- Database & Installation Info -->
                <x-card header="Technical Details" size="md" dark="false">
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Database</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Database Name</span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $installation->database->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Database User</span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $installation->databaseUser->username ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Installation</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Installed</span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">
                                        {{ $installation->installed_at ? $installation->installed_at->format('M d, Y') : 'Unknown' }}
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Full Path</span>
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-gray-900 dark:text-white break-all">
                                        {{ $installation->getFullPath() }}
                                    </code>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Admin URL</span>
                                    <a href="{{ $installation->getAdminUrl() }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 break-all">
                                        {{ $installation->getAdminUrl() }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Themes Tab -->
        <div x-show="tab === 'themes'" x-transition style="display: none;">
            @livewire('wordpress::theme-manager', ['installation' => $installation])
        </div>

        <!-- Plugins Tab -->
        <div x-show="tab === 'plugins'" x-transition style="display: none;">
            @livewire('wordpress::plugin-manager', ['installation' => $installation])
        </div>

        <!-- Updates Tab -->
        <div x-show="tab === 'updates'" x-transition style="display: none;">
            @livewire('wordpress::updates-manager', ['installation' => $installation])
        </div>

        <!-- Settings Tab -->
        <div x-show="tab === 'settings'" x-transition style="display: none;">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-card header="WordPress Settings" size="md" dark="false">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Configuration</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">WordPress settings</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Site Domain</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $installation->site->domain }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Installation Path</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $installation->path }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Locale</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $installation->locale }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Automatic Updates</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $installation->auto_update ? 'Enabled' : 'Disabled' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $installation->auto_update ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400' }}">
                                        {{ $installation->auto_update ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                            </div>

                            @if($installation->is_multisite)
                            <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border-2 border-purple-200 dark:border-purple-800">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-purple-900 dark:text-purple-300">Multisite Enabled</p>
                                        <p class="text-xs text-purple-700 dark:text-purple-400">This is a WordPress multisite installation</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </x-card>

                <x-card header="Site Information" size="md" dark="false">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Details</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Site configuration</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Server</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $installation->site->server->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $installation->site->server->ip ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Themes Installed</p>
                                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $installation->themes->count() }}</p>
                                    </div>
                                    <button @click="tab = 'themes'" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                        View →
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Plugins Installed</p>
                                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $installation->plugins->count() }}</p>
                                    </div>
                                    <button @click="tab = 'plugins'" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">
                                        View →
                                    </button>
                                </div>
                            </div>

                            @if($installation->hasUpdatesAvailable())
                            <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border-2 border-orange-200 dark:border-orange-800">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-orange-900 dark:text-orange-300">Updates Available</p>
                                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ $installation->getUpdatesCount() }}</p>
                                    </div>
                                    <button @click="tab = 'updates'" class="text-sm text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
                                        Update →
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.site') }}
@endsection



@section('content')
    <!-- Modern Breadcrumbs with Icons -->
    <div
        class="p-4 mb-6 bg-white border border-gray-200 dark:bg-gray-800/30 rounded-xl dark:border-gray-700/50 backdrop-blur-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">IP</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" id="siteip"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('spikster.aliases') }}</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" id="sitealiases"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">PHP</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" id="sitephp"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-900/20">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('spikster.site_base_path') }}</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">/home/<span
                            id="siteuserinfo"></span>/web/<span id="sitebasepathinfo"></span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        <!-- Basic Information -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('spikster.basic_information') }}
                </div>
            </x-slot>
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('spikster.domain') }}:
                    </label>
                    <input type="text" placeholder="e.g. domain.ltd" id="sitedomain" autocomplete="off"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('spikster.site_base_path') }}:
                    </label>
                    <input type="text" placeholder="e.g. public" id="sitebasepath" autocomplete="off"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                </div>
                <button type="button" id="updateSite"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ __('spikster.update') }}
                </button>
            </div>
        </x-card>

        <!-- Manage Aliases -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    {{ __('spikster.manage_aliases') }}
                </div>
            </x-slot>
            <div class="space-y-4">
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('spikster.add_alias') }}:</p>
                    <div class="flex gap-2">
                        <input type="text" placeholder="e.g. www.domain.ltd" id="siteaddalias" autocomplete="off"
                            class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" />
                        <button type="button" id="siteaddaliassubmit"
                            class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('spikster.aliases') }}:</p>
                    <div id="sitealiaseslist" class="flex flex-wrap gap-2"></div>
                </div>
            </div>
        </x-card>

        <!-- SSL Security -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    {!! __('spikster.ssl_security') !!}
                </div>
            </x-slot>
            <div class="space-y-4">
                <div>
                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">{{ __('spikster.ssl_security_text') }}:</p>
                    <button type="button" id="sitessl"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ __('spikster.ssl_generate') }}
                    </button>
                </div>
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('spikster.password_resets') }}:</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="sitesshreset"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-300 dark:border-yellow-600/50 text-yellow-700 dark:text-yellow-300 font-semibold rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/20 transform hover:scale-105 active:scale-95 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            SSH
                        </button>
                        <button type="button" id="sitemysqlreset"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/10 border border-blue-300 dark:border-blue-600/50 text-blue-700 dark:text-blue-300 font-semibold rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/20 transform hover:scale-105 active:scale-95 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                            MySql
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- GitHub Repository -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('spikster.github_repository') }}
                </div>
            </x-slot>
            <div class="space-y-4">
                <div>
                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">{{ __('spikster.github_repository_setup') }}
                    </p>
                    <div class="grid grid-cols-1 gap-2">
                        <button type="button" id="sitesetrepo"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('spikster.github_repository_config') }}
                        </button>
                        <button type="button" id="editdeploy"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            {{ __('spikster.github_repository_scripts') }}
                        </button>
                    </div>
                </div>
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('spikster.github_repository_deploy') }}:</p>
                    <div class="p-3 bg-gray-900 border border-gray-700 rounded-lg dark:bg-gray-950">
                        <code class="font-mono text-xs text-green-400">
                            ssh <span id="repodeployinfouser1"></span>@<span id="repodeployinfoip"></span><br />
                            sh /home/<span id="repodeployinfouser2"></span>/git/deploy.sh
                        </code>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Tools -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('spikster.tools') }}
                </div>
            </x-slot>
            <div class="space-y-4">
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('spikster.php_fpm_version') }}:</p>
                    <div class="flex gap-2">
                        <select id="sitephpver"
                            class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="8.3" id="php83">8.3</option>
                            <option value="8.2" id="php82">8.2</option>
                            <option value="8.1" id="php81">8.1</option>
                            <option value="8.0" id="php80">8.0</option>
                            <option value="7.4" id="php74">7.4</option>
                        </select>
                        <button type="button" id="sitephpversubmit"
                            class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Supervisor script:</p>
                    <input type="text" id="sitesupervisor" autocomplete="off"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all mb-3" />
                    <button type="button" id="sitesupervisorupdate"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('spikster.update') }}
                    </button>
                </div>
            </div>
        </x-card>

        <!-- MySQL Database -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    MYSQL
                </div>
            </x-slot>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Set up your database</p>
                <a href="{{ route('site.database', $site_id) }}" class="block">
                    <button type="button"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        DATABASE
                    </button>
                </a>
            </div>
        </x-card>

        <!-- WordPress Manager -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.051-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.763-7.572zm-5.203-2.62l3.246 8.9c-1.837-.87-3.288-2.43-4.093-4.315l.847-4.585zm11.68 1.17c0-.972-.349-1.646-.648-2.168-.399-.648-.772-1.197-.772-1.845 0-.723.548-1.395 1.32-1.395.035 0 .068.004.102.006C17.157 4.368 14.754 3.5 12 3.5c-3.399 0-6.39 1.742-8.128 4.382.228.007.443.011.623.011 1.013 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.53 10.5 2.119-6.357-1.508-4.143c-.522-.03-1.016-.092-1.016-.092-.522-.03-.461-.828.061-.798 0 0 1.601.123 2.552.123.987 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.506 10.426 1.294-4.32c.56-1.795.987-3.086.987-4.197zm.664-4.827c.034.251.053.52.053.81 0 .797-.149 1.692-.597 2.815L16.03 16.76c1.863-1.084 3.113-3.108 3.113-5.425 0-1.069-.268-2.073-.744-2.952l-.1.125zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 19.5c-5.238 0-9.5-4.262-9.5-9.5S6.762 2.5 12 2.5s9.5 4.262 9.5 9.5-4.262 9.5-9.5 9.5z" />
                    </svg>
                    Wordpress Manager
                </div>
            </x-slot>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Manage your project</p>
                <a href="{{ route('site.wordpress', $site_id) }}" class="block">
                    <button type="button"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.051-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.763-7.572zm-5.203-2.62l3.246 8.9c-1.837-.87-3.288-2.43-4.093-4.315l.847-4.585zm11.68 1.17c0-.972-.349-1.646-.648-2.168-.399-.648-.772-1.197-.772-1.845 0-.723.548-1.395 1.32-1.395.035 0 .068.004.102.006C17.157 4.368 14.754 3.5 12 3.5c-3.399 0-6.39 1.742-8.128 4.382.228.007.443.011.623.011 1.013 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.53 10.5 2.119-6.357-1.508-4.143c-.522-.03-1.016-.092-1.016-.092-.522-.03-.461-.828.061-.798 0 0 1.601.123 2.552.123.987 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.506 10.426 1.294-4.32c.56-1.795.987-3.086.987-4.197zm.664-4.827c.034.251.053.52.053.81 0 .797-.149 1.692-.597 2.815L16.03 16.76c1.863-1.084 3.113-3.108 3.113-5.425 0-1.069-.268-2.073-.744-2.952l-.1.125z" />
                        </svg>
                        Wordpress Manager
                    </button>
                </a>
            </div>
        </x-card>

        <!-- DNS Management -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                    </svg>
                    DNS Records
                </div>
            </x-slot>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Manage DNS records</p>
                <a href="{{ route('site.dns', $site_id) }}" class="block">
                    <button type="button"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                        </svg>
                        DNS MANAGEMENT
                    </button>
                </a>
            </div>
        </x-card>

        <!-- Email Management -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email Accounts
                </div>
            </x-slot>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Manage email accounts</p>
                <a href="{{ route('email.index', $site_id) }}" class="block">
                    <button type="button"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        EMAIL MANAGER
                    </button>
                </a>
            </div>
        </x-card>

        <!-- Backup & Restore -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    Backup & Restore
                </div>
            </x-slot>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Create and restore backups</p>
                <a href="{{ route('backups.index', $site_id) }}" class="block">
                    <button type="button"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        BACKUP MANAGER
                    </button>
                </a>
            </div>
        </x-card>

        <!-- Reset Permissions -->
        <x-card size="md" dark="false">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    File Permissions
                </div>
            </x-slot>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Fix file permission issues (chmod/chown)</p>
                <button type="button" id="resetPermissionsBtn"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span id="resetPermissionsText">RESET PERMISSIONS</span>
                </button>
            </div>
        </x-card>

        {{-- <div class="col-span-1 md:col-span-2">
        <div class="h-full card">
            <div class="card-header">
                <i class="mr-1 fas fa-rocket fs-fw"></i>
                File Manager
            </div>
            <div class="text-center card-body">
                <div class="space"></div>
                <form action="{{route('files.index')}}" method="get">
                    <input type="hidden" name="site-uuid" id="siteuuid">
                    <input type="submit" value="Open Manager" class="btn btn-primary">
                </form>
                <div class="space"></div>
            </div>
        </div>
    </div> --}}

        {{-- <div id="nodejsManager" class="col-span-1 md:col-span-2 d-none">
        <div class="h-full card">
            <div class="card-header">
                <i class="mr-1 fab fa-github fs-fw"></i>
                Nodejs Manager
            </div>
            <div class="card-body">
                <p class="mb-2">Manage your project</p>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" style="min-width:200px" id="startNodejsButton">Setup App</button>
                    <div class="space"></div>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" style="min-width:200px" id="stopNodeButton">Stop App</button>
                    <div class="space"></div>
                </div>

            </div>
        </div>
    </div> --}}
    </div>
@endsection



@section('extra')
    <input type="hidden" id="currentdomain">
    <input type="hidden" id="server_id">

    <!-- Repository Modal - Tailwind -->
    <dialog class="fixed inset-0 z-50 hidden overflow-y-auto" id="repositoryModal"
        aria-labelledby="repositoryModalLabel">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-xl dark:bg-gray-800 rounded-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                id="repositorydialog">
                <div class="px-6 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="repositoryModalLabel">
                            {{ __('spikster.github_repository') }}
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            onclick="document.getElementById('repositoryModal').close()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label for="repositoryproject"
                                class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('spikster.repository_project') }}
                            </label>
                            <input
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                type="text" id="repositoryproject" placeholder="e.g. johndoe/helloworld"
                                autocomplete="off" />
                        </div>
                        <div>
                            <label for="repositorybranch"
                                class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('spikster.repository_branch') }}
                            </label>
                            <input
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                type="text" id="repositorybranch" placeholder="e.g. develop" autocomplete="off" />
                        </div>
                        <div>
                            <label for="deploykey"
                                class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('spikster.repository_deploy_key') }} {!! __('spikster.repository_deploy_key_info') !!}
                            </label>
                            <textarea id="deploykey" readonly
                                class="w-full h-36 px-4 py-2.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white font-mono"></textarea>
                        </div>
                        <div class="pt-2 text-center">
                            <button
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200"
                                type="button" id="repositorysubmit">{{ __('spikster.confirm') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    <!-- Deploy Scripts Modal - Tailwind -->
    <dialog class="fixed inset-0 z-50 hidden overflow-y-auto" id="deployModal" aria-labelledby="deployModalLabel">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-xl dark:bg-gray-800 rounded-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="deployModalLabel">
                            {{ __('spikster.deploy_scripts') }}
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            onclick="document.getElementById('deployModal').close()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-6">
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('spikster.github_repository_scripts') }}:</p>
                        <div id="deploy" style="height:250px;width:100%;"></div>
                        <div class="mt-6 text-center">
                            <button
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200"
                                type="button" id="deploysubmit">{{ __('spikster.save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    <!-- SSH Reset Modal - Tailwind -->
    <dialog class="fixed inset-0 z-50 hidden overflow-y-auto" id="sshresetModal" aria-labelledby="sshresetModalLabel">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-xl dark:bg-gray-800 rounded-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="sshresetModalLabel">
                            {{ __('spikster.require_password_reset_modal_title') }}
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            onclick="document.getElementById('sshresetModal').close()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-6">
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('spikster.require_ssh_password_reset_modal_text') }}</p>
                        <div class="mt-6 text-center">
                            <button
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200"
                                type="button" id="sshresetsubmit">{{ __('spikster.confirm') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    <!-- MySQL Reset Modal - Tailwind -->
    <dialog class="fixed inset-0 z-50 hidden overflow-y-auto" id="mysqlresetModal"
        aria-labelledby="mysqlresetModalLabel">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white shadow-xl dark:bg-gray-800 rounded-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="mysqlresetModalLabel">
                            {{ __('spikster.require_password_reset_modal_title') }}
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            onclick="document.getElementById('mysqlresetModal').close()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-6">
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('spikster.require_mysql_password_reset_modal_text') }}</p>
                        <div class="mt-6 text-center">
                            <button
                                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200"
                                type="button" id="mysqlresetsubmit">{{ __('spikster.confirm') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>
@endsection



@section('css')
@endsection



@section('js')
    <script>
        // Get Server info
        $('#mainloading').removeClass('d-none');

        // Site Init
        function siteInit() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}',
                type: 'GET',
                success: function(data) {
                    $('#mainloading').addClass('d-none');
                    $('#siteip').html(data.server_ip);
                    $('#sitealiases').html(data.aliases);
                    $('#sitephp').html(data.php);
                    $('#sitebasepathinfo').html(data.basepath);
                    $('#siteuserinfo').html(data.username);
                    $('#maintitle').html('- ' + data.domain);
                    $('#sitedomain').val(data.domain);
                    $('#sitebasepath').val(data.basepath);
                    $('#siteuuid').val(data.rootpath);
                    $('#currentdomain').val(data.domain);
                    $('#server_id').val(data.server_id);
                    $('#sitesupervisor').val(data.supervisor);
                    $('#deploykey').html(data.deploy_key)
                    $('#repodeployinfouser1').html(data.username);
                    $('#repodeployinfouser2').html(data.username);
                    $('#repodeployinfoip').html(data.server_ip);
                    $('#repositoryproject').val(data.repository);
                    $('#repositorybranch').val(data.branch);
                    deploy.session.setValue(data.deploy);
                    getDataNoDT('/api/servers/' + data.server_id + '/domains');
                    switch (data.php) {
                        case '8.3':
                            $('#php83').attr("selected", "selected");
                            break;
                        case '8.2':
                            $('#php82').attr("selected", "selected");
                            break;
                        case '8.1':
                            $('#php81').attr("selected", "selected");
                            break;
                        case '8.0':
                            $('#php80').attr("selected", "selected");
                            break;
                        case '7.4':
                            $('#php74').attr("selected", "selected");
                            break;
                        case '7.3':
                            // Append legacy php 7.3
                            $('#phpver').append('<option value="7.3" selected>7.3</option>');
                            break;
                        default:
                            break;
                    }
                },
            });
            $.ajax({
                url: '/api/sites/{{ $site_id }}/aliases',
                type: 'GET',
                success: function(data) {
                    $('#sitealiaseslist').empty();
                    jQuery(data).each(function(i, item) {
                        $('#sitealiaseslist').append(
                            '<span class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700/50 text-purple-700 dark:text-purple-300 text-sm rounded-lg">' +
                            item.domain + '<button data-id="' + item.alias_id +
                            '" class="transition-colors sitealiasdel hover:text-purple-900 dark:hover:text-purple-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></span>'
                        );
                    });
                },
            });
        }
        $(document).ajaxSuccess(function() {
            aliasesDelete();
        });

        // Init variables
        siteInit();

        // Password reset
        $('#sitesshreset').click(function() {
            $('#sshresetModal').modal();
        });
        $('#sshresetsubmit').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}/reset/ssh',
                type: 'POST',
                beforeSend: function() {
                    $('#sshresetloading').removeClass('d-none');
                },
                success: function(data) {
                    success('{{ __('spikster.new_ssh_password_success') }}:<br><b>' + data.password +
                        '</b><br><a href="' + data.pdf +
                        '" target="_blank" style="color:#ffffff">{{ __('spikster.download_site_data') }}</a>'
                    );
                    $('#sshresetloading').addClass('d-none');
                    $('#sshresetModal').modal('toggle');
                    $(window).scrollTop(0);
                }
            });
        });

        // DB Password reset
        $('#sitemysqlreset').click(function() {
            $('#mysqlresetModal').modal();
        });
        $('#mysqlresetsubmit').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}/reset/db',
                type: 'POST',
                beforeSend: function() {
                    $('#mysqlresetloading').removeClass('d-none');
                },
                success: function(data) {
                    success('{{ __('spikster.new_mysql_password_success') }}:<br><b>' + data.password +
                        '</b><br><a href="' + data.pdf +
                        '" target="_blank" style="color:#ffffff">{{ __('spikster.download_site_data') }}</a>'
                    );
                    $('#mysqlresetloading').addClass('d-none');
                    $('#mysqlresetModal').modal('toggle');
                    $(window).scrollTop(0);
                }
            });
        });

        // SSLs Require
        $('#sitessl').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}/ssl',
                type: 'POST',
                beforeSend: function() {
                    $('#sitesslloading').removeClass('d-none');
                },
                success: function(data) {
                    $('#sitesslloading').addClass('d-none');
                }
            });
        });


        // Repository
        $('#sitesetrepo').click(function() {
            $('#repositoryModal').modal();
        });

        // Repository Submit
        $('#repositorysubmit').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}',
                type: 'PATCH',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'repository': $('#repositoryproject').val(),
                    'branch': $('#repositorybranch').val(),
                }),
                beforeSend: function() {
                    $('#repositoryloading').removeClass('d-none');
                },
                success: function(data) {
                    $('#repositoryloading').addClass('d-none');
                    $('#repositoryModal').modal('toggle');
                    siteInit();
                },
            });
        });

        //Deploy Key Copy
        $("#copykey").click(function() {
            $("#deploykey").select();
            document.execCommand('copy');
        });

        // Deploy editor
        var deploy = ace.edit("deploy");
        deploy.setTheme("ace/theme/monokai");
        deploy.session.setMode("ace/mode/sh");

        // Deploy Edit
        $('#editdeploy').click(function() {
            $('#deployModal').modal();
        });

        // Deploy Submit
        $('#deploysubmit').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}',
                type: 'PATCH',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'deploy': deploy.getSession().getValue(),
                }),
                beforeSend: function() {
                    $('#deployloading').removeClass('d-none');
                },
                success: function(data) {
                    $('#deployloading').addClass('d-none');
                    $('#deployModal').modal('toggle');
                    siteInit();
                },
            });
        });


        //Check Domain Conflict
        function domainConflict(domain) {
            conflict = 0;
            JSON.parse(localStorage.otherdata).forEach(item => {
                if (item == domain) {
                    conflict = conflict + 1;
                }
            });
            return conflict;
        }


        // Site Aliases
        $('#siteaddalias').keyup(function() {
            $('#siteaddalias').removeClass('border-red-500').addClass('border-gray-200 dark:border-gray-600');
        });
        $('#siteaddaliassubmit').click(function() {
            if (domainConflict($('#siteaddalias').val()) < 1 && $('#siteaddalias').val() != '') {
                $.ajax({
                    url: '/api/sites/{{ $site_id }}/aliases',
                    type: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        'domain': $('#siteaddalias').val(),
                    }),
                    beforeSend: function() {
                        $('#siteaddaliassubmit').html(
                            '<svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                        );
                    },
                    success: function(data) {
                        $('#siteaddalias').val('');
                        $('#siteaddaliassubmit').empty();
                        $('#siteaddaliassubmit').html(
                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>'
                        );
                        siteInit();
                    },
                });
            } else {
                $('#siteaddalias').removeClass('border-gray-200 dark:border-gray-600').addClass('border-red-500');
            }
        });

        //Delete Aliases
        function aliasesDelete() {
            $(".sitealiasdel").on("click", function() {
                $.ajax({
                    url: '/api/sites/{{ $site_id }}/aliases/' + $(this).attr('data-id'),
                    type: 'DELETE',
                    success: function(data) {
                        $('#mainloading').removeClass('d-none');
                    },
                    complete: function() {
                        setTimeout(() => {
                            siteInit();
                        }, 5000);
                    }
                });
            });
        }

        // Change PHP
        $('#sitephpversubmit').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}',
                type: 'PATCH',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'php': $('#sitephpver').val(),
                }),
                beforeSend: function() {
                    $('#sitephpversubmit').html(
                        '<svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                    );
                },
                success: function(data) {
                    $('#sitephpversubmit').empty();
                    $('#sitephpversubmit').html(
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>'
                    );
                    siteInit();
                },
            });
        });

        // Supervisor
        $('#sitesupervisorupdate').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}',
                type: 'PATCH',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'supervisor': $('#sitesupervisor').val(),
                }),
                beforeSend: function() {
                    $('#sitesupervisorupdateloading').removeClass('d-none');
                },
                success: function(data) {
                    $('#sitesupervisorupdateloading').addClass('d-none');
                    siteInit();
                },
            });
        });

        // Basic info
        $('#updateSite').click(function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}',
                type: 'PATCH',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'domain': $('#sitedomain').val(),
                    'basepath': $('#sitebasepath').val(),
                }),
                beforeSend: function() {
                    $('#updateSiteloadingloading').removeClass('d-none');
                },
                success: function(data) {
                    $('#updateSiteloadingloading').addClass('d-none');
                    siteInit();
                },
            });
        });
    </script>
@endsection

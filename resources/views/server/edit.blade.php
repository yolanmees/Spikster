@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.server') }}
@endsection



@section('content')
    <div x-data="{ tab: 'monitor' }">
        <x-page-header title="{{ __('spikster.titles.server') }}">
            <x-slot name="actions">
                <x-back-button :href="route('servers.index')" />
                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <span>IP: <b id="serveriptop" class="text-gray-900 dark:text-white"></b></span>
                    <span>{{ __('spikster.sites') }}: <b id="serversites" class="text-gray-900 dark:text-white"></b></span>
                    <span class="flex items-center gap-1">Ping: <span id="serverping">
                        <svg class="animate-spin h-4 w-4 inline-block text-gray-400" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span></span>
                </div>
            </x-slot>
        </x-page-header>

        <x-alpine-tabs model="tab" :tabs="[
            ['key' => 'monitor',  'label' => 'Monitor',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\'/></svg>'],
            ['key' => 'server',   'label' => 'Server information',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01\'/></svg>'],
            ['key' => 'security', 'label' => 'Security',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z\'/></svg>'],
            ['key' => 'tools',    'label' => 'Tools',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/></svg>'],
        ]" />



        <div x-show="tab === 'monitor'" x-transition>
            <x-server-monitoring-charts :serverId="$server_id" />
        </div>


        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" x-show="tab === 'server'" x-transition
            style="display: none;">
            <x-card header="{{ __('spikster.server_information') }}" size="md" dark="false">
                <livewire:server.server-information :server_id="$server_id" />
            </x-card>
            <x-card header="{{ __('spikster.system_services') }}" size="md" dark="false">
                <livewire:server.system-services :server_id="$server_id" />
            </x-card>
            <x-card header="Server Logs" size="md" dark="false">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-600 to-gray-800 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Log Files</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">View server logs</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Access and review system logs, error logs, and access logs for debugging and monitoring.
                    </p>

                    <a href="{{ route('logs.index', ['server_id' => $server_id]) }}" class="block">
                        <button type="button"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-gray-300 dark:border-gray-600 text-sm font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300/50 transform hover:scale-105 active:scale-95 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Open Logs
                        </button>
                    </a>

                    <div class="pt-2">
                        <div class="bg-gray-50 dark:bg-gray-800/50 border-l-4 border-gray-400 p-3 rounded">
                            <p class="text-xs text-gray-600 dark:text-gray-400 flex items-start gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Logs are updated in real-time and can help diagnose issues.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-show="tab === 'security'" x-transition
            style="display: none;">
            <x-card header="Security & Protection" size="md" dark="false">
                <div class="space-y-6">
                    <!-- Fail2ban Section -->
                    <div class="relative group">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Fail2ban</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Monitor and manage blocked IP
                                    addresses to protect your server from brute-force attacks.</p>
                                <x-action-button :href="route('server.fail2ban', $server_id)"
                                    class="inline-flex items-center gap-2 transform hover:scale-105 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Open Fail2ban
                                </x-action-button>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Additional Security Features Card -->
            <x-card header="Server Health" size="md" dark="false">
                <div class="space-y-4">
                    <!-- Security Status -->
                    <div
                        class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border-2 border-green-200 dark:border-green-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Active Protection</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Firewall & monitoring enabled</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Items -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">SSH Protection</span>
                            </div>
                            <span
                                class="text-xs font-semibold text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded">Active</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Firewall Rules</span>
                            </div>
                            <span
                                class="text-xs font-semibold text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded">Configured</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto Updates</span>
                            </div>
                            <span
                                class="text-xs font-semibold text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded">Enabled</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" x-show="tab === 'tools'" x-transition
            style="display: none;">
            <!-- PHP CLI Version Card -->
            <x-card header="PHP Configuration" size="md" dark="false">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">CLI Version</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Command Line Interface</p>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2">
                            {{ __('spikster.php_cli_version') }}
                        </label>
                        <div class="flex gap-2">
                            <select id="phpver"
                                class="flex-1 px-4 py-2.5 rounded-lg border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all duration-200">
                                <option value="8.3" id="php83">PHP 8.3</option>
                                <option value="8.2" id="php82">PHP 8.2</option>
                                <option value="8.1" id="php81">PHP 8.1</option>
                                <option value="8.0" id="php80">PHP 8.0</option>
                                <option value="7.4" id="php74">PHP 7.4</option>
                            </select>
                            <button type="button" id="changephp"
                                class="inline-flex items-center justify-center px-4 py-2.5 border-2 border-purple-500 dark:border-purple-600 rounded-lg text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 focus:outline-none focus:ring-4 focus:ring-purple-500/50 transform hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-3 rounded">
                            <p class="text-xs text-blue-700 dark:text-blue-300 flex items-start gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>This changes the default PHP version for CLI commands.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Cron Jobs Card -->
            <x-card header="Scheduled Tasks" size="md" dark="false">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Cron Jobs</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Automated tasks</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('spikster.manage_cron_jobs') }}
                        </p>
                        <a href="{{ route('server.cron', $server_id) }}">
                            <x-primary-button type="button"
                                class="w-full inline-flex items-center justify-center gap-2 transform hover:scale-105 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ __('spikster.edit_crontab') }}
                            </x-primary-button>
                        </a>
                    </div>

                    <div class="pt-2">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-3 rounded">
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 flex items-start gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Be careful when editing cron jobs. Incorrect syntax may break scheduled tasks.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Password Reset Card -->
            <x-card header="System Access" size="md" dark="false">
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Password Reset</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Security credentials</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('spikster.reset_cipi_password') }}
                        </p>
                        <x-danger-button type="button" id="rootreset"
                            class="w-full inline-flex items-center justify-center gap-2 transform hover:scale-105 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            {{ __('spikster.require_reset_cipi_password') }}
                        </x-danger-button>
                    </div>

                    <div class="pt-2">
                        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-3 rounded">
                            <p class="text-xs text-red-700 dark:text-red-300 flex items-start gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>This will generate a new password for the server user. Store it safely.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection



@section('extra')
    {{-- Crontab Modal --}}
    <x-modal id="crontab-modal" title="{{ __('spikster.server_crontab') }}" max-width="2xl">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ __('spikster.server_crontab_edit') }}:</p>
        <div id="crontab" style="height:250px;width:100%;border-radius:0.5rem;overflow:hidden;"></div>
        <x-slot name="footer">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            <x-primary-button id="crontabsubmit">{{ __('spikster.save') }}</x-primary-button>
        </x-slot>
    </x-modal>

    {{-- Root Reset Modal --}}
    <x-modal id="root-reset-modal" title="{{ __('spikster.require_password_reset_modal_title') }}" max-width="lg">
        <p class="text-sm text-gray-700 dark:text-gray-300">
            {{ __('spikster.require_password_reset_modal_text') }}
        </p>
        <x-slot name="footer">
            <x-secondary-button @click="open = false">Cancel</x-secondary-button>
            <x-danger-button id="rootresetsubmit">{{ __('spikster.confirm') }}</x-danger-button>
        </x-slot>
    </x-modal>
@endsection

@section('css')
@endsection



@section('js')
    <script>
        // Get Server info
        $('#mainloading').removeClass('d-none');

        // Crontab editor
        var crontab = ace.edit("crontab");
        crontab.setTheme("ace/theme/monokai");
        crontab.session.setMode("ace/mode/sh");

        // Crontab edit
        $('#editcrontab').click(function() {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'crontab-modal' }));
        });

        // Crontab Submit
        $('#crontabsubmit').click(function() {
            $.ajax({
                url: '/api/servers/{{ $server_id }}',
                type: 'PATCH',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'cron': crontab.getSession().getValue(),
                }),
                success: function(data) {
                    window.dispatchEvent(new CustomEvent('close-modal'));
                    serverInit();
                },
            });
        });

        // Server Init
        function serverInit() {
            getDataNoDT('/api/servers', false);
            $.ajax({
                url: '/api/servers/{{ $server_id }}',
                type: 'GET',
                success: function(data) {
                    $('#mainloading').addClass('d-none');
                    $('#serveriptop').html(data.ip);
                    $('#serversites').html(data.sites);
                    $('#maintitle').html('- ' + data.name);
                    crontab.session.setValue(data.cron);
                    $('#serverbuild').empty();
                    if (data.build) {
                        $('#serverbuild').html(data.build);
                    } else {
                        $('#serverbuild').html('{{ __('spikster.unknown') }}');
                    }
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
        }

        // Init variables
        serverInit();

        // Ping
        function getPing() {
            $.ajax({
                url: '/api/servers/{{ $server_id }}/ping',
                type: 'GET',
                timeout: 10000, // 10 second timeout
                beforeSend: function() {
                    $('#serverping').empty();
                    $('#serverping').html(
                        '<svg class="animate-spin h-4 w-4 inline-block text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                    );
                },
                success: function(data) {
                    $('#serverping').empty();
                    if (data.status === 'online') {
                        $('#serverping').html(
                            '<svg class="w-4 h-4 inline-block text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />' +
                            '</svg>'
                        );
                    } else {
                        $('#serverping').html(
                            '<svg class="w-4 h-4 inline-block text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />' +
                            '</svg>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('#serverping').empty();
                    $('#serverping').html(
                        '<svg class="w-4 h-4 inline-block text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />' +
                        '</svg>'
                    );
                }
            });
        }
        setInterval(function() {
            getPing();
        }, 10000);
        getPing();

        // Change PHP
        $('#changephp').click(function() {
            $.ajax({
                url: '/api/servers/{{ $server_id }}',
                type: 'PATCH',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'php': $('#phpver').val(),
                }),
                beforeSend: function() {
                    $('#changephp').html(
                        '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                    );
                },
                success: function(data) {
                    $('#changephp').empty();
                    $('#changephp').html(
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>'
                    );
                },
            });
            serverInit();
        });

        // Root Reset
        $('#rootreset').click(function() {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'root-reset-modal' }));
        });

        // Root Reset Submit
        $('#rootresetsubmit').click(function() {
            $.ajax({
                url: '/api/servers/{{ $server_id }}/rootreset',
                type: 'POST',
                success: function(data) {
                    success('{{ __('spikster.new_password_success') }}:<br><b>' + data.password +
                        '</b>');
                    $(window).scrollTop(0);
                    window.dispatchEvent(new CustomEvent('close-modal'));
                }
            });
        });

        // Charts style
        Chart.defaults.global.defaultFontFamily =
            '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#292b2c';
    </script>
@endsection

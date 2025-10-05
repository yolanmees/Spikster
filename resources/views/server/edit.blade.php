@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.server') }}
@endsection



@section('content')
    <div x-data="{ tab: 'monitor' }">
        <ol class="breadcrumbs">
            <li class="breadcrumb-item active">IP:<b><span class="ml-1" id="serveriptop"></span></b></li>
            <li class="breadcrumb-item active">{{ __('spikster.sites') }}:<b><span class="ml-1" id="serversites"></span></b>
            </li>
            <li class="breadcrumb-item active">Ping:<b><span class="ml-1" id="serverping"><i
                            class="fas fa-circle-notch fa-spin"></i></span></b></li>
        </ol>

        <div class="pb-6">
            <div class="sm:hidden">
                <label for="tabs" class="sr-only">Select a tab</label>
                <select id="tabs" name="tabs" x-model="tab"
                    class="block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                    <option value="monitor">Monitor</option>
                    <option value="server">Server information</option>
                    <option value="security">Security</option>
                    <option value="tools">Tools</option>
                </select>
            </div>
            <div class="hidden sm:block">
                <div class="border-b-2 border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px space-x-6" aria-label="Tabs">
                        <button @click="tab = 'monitor'"
                            :class="tab === 'monitor' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'monitor' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor
                        </button>
                        <button @click="tab = 'server'"
                            :class="tab === 'server' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'server' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                            Server information
                        </button>
                        <button @click="tab = 'security'"
                            :class="tab === 'security' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'security' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Security
                        </button>
                        <button @click="tab = 'tools'"
                            :class="tab === 'tools' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'tools' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Tools
                        </button>
                    </nav>
                </div>
            </div>
        </div>



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

                    <a href="{{ route('logs', $server_id) }}" class="block">
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
                        <x-primary-button type="button" id="editcrontab"
                            class="w-full inline-flex items-center justify-center gap-2 transform hover:scale-105 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('spikster.edit_crontab') }}
                        </x-primary-button>
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
    <!-- Crontab Modal -->
    <div x-data="{ showCrontabModal: false }" x-show="showCrontabModal" x-cloak id="crontabModalContainer"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCrontabModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="showCrontabModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showCrontabModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('spikster.server_crontab') }}
                        </h3>
                        <button type="button" @click="showCrontabModal = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <x-icon icon="x" class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ __('spikster.server_crontab_edit') }}:
                        </p>
                        <div id="crontab" style="height:250px;width:100%;"></div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <x-primary-button type="button" id="crontabsubmit" class="sm:ml-3">
                        {{ __('spikster.save') }}
                    </x-primary-button>
                    <x-secondary-button type="button" @click="showCrontabModal = false">
                        Cancel
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </div>

    <!-- Root Reset Modal -->
    <div x-data="{ showRootResetModal: false }" x-show="showRootResetModal" x-cloak id="rootresetModalContainer"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showRootResetModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="showRootResetModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showRootResetModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('spikster.require_password_reset_modal_title') }}
                        </h3>
                        <button type="button" @click="showRootResetModal = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <x-icon icon="x" class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('spikster.require_password_reset_modal_text') }}</p>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <x-danger-button type="button" id="rootresetsubmit" class="sm:ml-3">
                        {{ __('spikster.confirm') }}
                    </x-danger-button>
                    <x-secondary-button type="button" @click="showRootResetModal = false">
                        Cancel
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </div>
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
            Alpine.store('modals', {
                showCrontabModal: true
            });
            document.querySelector('#crontabModalContainer').__x.$data.showCrontabModal = true;
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
                    document.querySelector('#crontabModalContainer').__x.$data.showCrontabModal = false;
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
                beforeSend: function() {
                    $('#serverping').empty();
                    $('#serverping').html(
                        '<i class="fas fa-circle-notch fa-spin" title="{{ __('spikster.loading_data') }}"></i>'
                    );
                },
                success: function(data) {
                    $('#serverping').empty();
                    $('#serverping').html('<i class="fas fa-check text-success"></i>');
                },
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
                        '<i class="fas fa-circle-notch fa-spin" title="{{ __('spikster.loading_please_wait') }}"></i>'
                    );
                },
                success: function(data) {
                    $('#changephp').empty();
                    $('#changephp').html('<i class="fas fas fa-edit"></i>');
                },
            });
            serverInit();
        });

        // Root Reset
        $('#rootreset').click(function() {
            document.querySelector('#rootresetModalContainer').__x.$data.showRootResetModal = true;
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
                    document.querySelector('#rootresetModalContainer').__x.$data.showRootResetModal =
                        false;
                }
            });
        });

        // Charts style
        Chart.defaults.global.defaultFontFamily =
            '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#292b2c';
    </script>
@endsection

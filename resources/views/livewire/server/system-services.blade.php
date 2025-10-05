<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4">
            <livewire:components.alert type="success" :message="session('success')" :dismissible="true" />
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4">
            <livewire:components.alert type="error" :message="session('error')" :dismissible="true" />
        </div>
    @endif

    <div class="space-y-3">
        <!-- nginx -->
        <div class="group relative bg-white dark:bg-gray-800/30 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50 hover:border-green-200 dark:hover:border-green-800/50 hover:shadow-sm transition-all duration-200">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">nginx</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Web Server</p>
                    </div>
                </div>
                <button type="button" wire:click="restartService('nginx')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 border border-yellow-300 dark:border-yellow-600/50 text-sm font-semibold rounded-lg text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/10 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 focus:outline-none focus:ring-4 focus:ring-yellow-300/20 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95 transition-all duration-200"
                    wire:loading.attr="disabled"
                    wire:target="restartService('nginx')">
                    <span wire:loading.remove wire:target="restartService('nginx')" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('spikster.restart') }}
                    </span>
                    <span wire:loading wire:target="restartService('nginx')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Restarting...
                    </span>
                </button>
            </div>
        </div>

        <!-- PHP-FPM -->
        <div class="group relative bg-white dark:bg-gray-800/30 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50 hover:border-purple-200 dark:hover:border-purple-800/50 hover:shadow-sm transition-all duration-200">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">PHP-FPM</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">PHP Processor</p>
                    </div>
                </div>
                <button type="button" wire:click="restartService('php')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 border border-yellow-300 dark:border-yellow-600/50 text-sm font-semibold rounded-lg text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/10 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 focus:outline-none focus:ring-4 focus:ring-yellow-300/20 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95 transition-all duration-200"
                    wire:loading.attr="disabled"
                    wire:target="restartService('php')">
                    <span wire:loading.remove wire:target="restartService('php')" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('spikster.restart') }}
                    </span>
                    <span wire:loading wire:target="restartService('php')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Restarting...
                    </span>
                </button>
            </div>
        </div>

        <!-- MySql -->
        <div class="group relative bg-white dark:bg-gray-800/30 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50 hover:border-blue-200 dark:hover:border-blue-800/50 hover:shadow-sm transition-all duration-200">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">MySql</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Database Server</p>
                    </div>
                </div>
                <button type="button" wire:click="restartService('mysql')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 border border-yellow-300 dark:border-yellow-600/50 text-sm font-semibold rounded-lg text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/10 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 focus:outline-none focus:ring-4 focus:ring-yellow-300/20 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95 transition-all duration-200"
                    wire:loading.attr="disabled"
                    wire:target="restartService('mysql')">
                    <span wire:loading.remove wire:target="restartService('mysql')" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('spikster.restart') }}
                    </span>
                    <span wire:loading wire:target="restartService('mysql')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Restarting...
                    </span>
                </button>
            </div>
        </div>

        <!-- Redis -->
        <div class="group relative bg-white dark:bg-gray-800/30 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50 hover:border-red-200 dark:hover:border-red-800/50 hover:shadow-sm transition-all duration-200">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Redis</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Cache Server</p>
                    </div>
                </div>
                <button type="button" wire:click="restartService('redis')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 border border-yellow-300 dark:border-yellow-600/50 text-sm font-semibold rounded-lg text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/10 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 focus:outline-none focus:ring-4 focus:ring-yellow-300/20 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95 transition-all duration-200"
                    wire:loading.attr="disabled"
                    wire:target="restartService('redis')">
                    <span wire:loading.remove wire:target="restartService('redis')" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('spikster.restart') }}
                    </span>
                    <span wire:loading wire:target="restartService('redis')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Restarting...
                    </span>
                </button>
            </div>
        </div>

        <!-- Supervisor -->
        <div class="group relative bg-white dark:bg-gray-800/30 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50 hover:border-indigo-200 dark:hover:border-indigo-800/50 hover:shadow-sm transition-all duration-200">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Supervisor</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Process Manager</p>
                    </div>
                </div>
                <button type="button" wire:click="restartService('supervisor')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 border border-yellow-300 dark:border-yellow-600/50 text-sm font-semibold rounded-lg text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/10 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 focus:outline-none focus:ring-4 focus:ring-yellow-300/20 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95 transition-all duration-200"
                    wire:loading.attr="disabled"
                    wire:target="restartService('supervisor')">
                    <span wire:loading.remove wire:target="restartService('supervisor')" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('spikster.restart') }}
                    </span>
                    <span wire:loading wire:target="restartService('supervisor')" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Restarting...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

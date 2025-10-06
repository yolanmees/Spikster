<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Email Management
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $site->domain }} - Manage email accounts, forwarders, and settings
                </p>
            </div>
            <a href="{{ route('site.edit', $site->site_id) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Site
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div x-data="{ activeTab: 'accounts' }" class="space-y-6">
                <!-- Tabs -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'accounts'"
                            :class="activeTab === 'accounts' ? 'border-green-500 text-green-600 dark:text-green-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Email Accounts
                        </button>
                        <button @click="activeTab = 'forwarders'"
                            :class="activeTab === 'forwarders' ? 'border-green-500 text-green-600 dark:text-green-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            Forwarders
                        </button>
                        <button @click="activeTab = 'settings'"
                            :class="activeTab === 'settings' ? 'border-green-500 text-green-600 dark:text-green-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="mt-6">
                    <!-- Email Accounts Tab -->
                    <div x-show="activeTab === 'accounts'" x-cloak>
                        @livewire('email.email-accounts-table', ['siteId' => $site->site_id])
                    </div>

                    <!-- Forwarders Tab -->
                    <div x-show="activeTab === 'forwarders'" x-cloak>
                        @livewire('email.email-forwarders-table', ['siteId' => $site->site_id])
                    </div>

                    <!-- Settings Tab -->
                    <div x-show="activeTab === 'settings'" x-cloak>
                        <div class="space-y-6">
                            <!-- DKIM Configuration -->
                            <div
                                class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">DKIM
                                        Configuration</h3>
                                    @livewire('email.email-dkim-manager', ['siteId' => $site->site_id])
                                </div>
                            </div>

                            <!-- Email Server Information -->
                            <div
                                class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Server
                                        Information</h3>
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Incoming Mail (IMAP) -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-gray-900 dark:text-white">Incoming Mail
                                                    Server (IMAP)</h4>
                                                <div class="space-y-1 text-sm">
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">Server:</span> {{ $site->server->ip }}
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">Port:</span> 993 (SSL/TLS)
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">Security:</span> SSL/TLS
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Outgoing Mail (SMTP) -->
                                            <div class="space-y-2">
                                                <h4 class="font-medium text-gray-900 dark:text-white">Outgoing Mail
                                                    Server (SMTP)</h4>
                                                <div class="space-y-1 text-sm">
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">Server:</span> {{ $site->server->ip }}
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">Port:</span> 587 (STARTTLS) or 465
                                                        (SSL/TLS)
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">Security:</span> STARTTLS or SSL/TLS
                                                    </p>
                                                    <p class="text-gray-600 dark:text-gray-400">
                                                        <span class="font-medium">Authentication:</span> Required
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Use your full email address as the username (e.g.,
                                                user@{{ $site - > domain }})
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

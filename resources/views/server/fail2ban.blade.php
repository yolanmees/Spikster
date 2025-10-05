@extends('layouts.app')

@section('title')
    {{ __('spikster.titles.server') }} - Fail2ban
@endsection

@section('content')
    <div x-data="{
        tab: 'banned-ips',
        deploying: false,
        deploySuccess: false,
        deployError: null,
        async deployApi() {
            this.deploying = true;
            this.deploySuccess = false;
            this.deployError = null;
    
            try {
                const response = await fetch('/api/servers/{{ $server_id }}/fail2ban/deploy', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
    
                const data = await response.json();
    
                if (response.ok) {
                    this.deploySuccess = true;
                    setTimeout(() => { this.deploySuccess = false; }, 5000);
                } else {
                    this.deployError = data.message || 'Failed to deploy API';
                }
            } catch (error) {
                this.deployError = 'Network error: ' + error.message;
            } finally {
                this.deploying = false;
            }
        }
    }">
        <!-- Breadcrumbs Header -->
        <ol class="breadcrumbs">
            <li class="breadcrumb-item active">Server:<b><span class="ml-1">{{ $server_id }}</span></b></li>
            <li class="breadcrumb-item active">Fail2ban Management</li>
        </ol>

        <!-- Deploy API Notice/Button -->
        <div class="mb-4">
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            Fail2ban API Setup Required
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>The Fail2ban API must be deployed to your server before using these features. Click the
                                button below to install it.</p>
                        </div>
                        <div class="mt-4">
                            <button @click="deployApi()" :disabled="deploying"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                                <svg x-show="deploying" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="deploying ? 'Deploying...' : 'Deploy Fail2ban API'"></span>
                            </button>
                        </div>

                        <!-- Success Message -->
                        <div x-show="deploySuccess" x-transition
                            class="mt-3 flex items-center text-sm font-medium text-green-700 dark:text-green-300">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            API deployed successfully!
                        </div>

                        <!-- Error Message -->
                        <div x-show="deployError" x-transition
                            class="mt-3 flex items-start text-sm text-red-700 dark:text-red-300">
                            <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span x-text="deployError"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pb-6">
            <!-- Mobile Tabs -->
            <div class="sm:hidden">
                <label for="tabs" class="sr-only">Select a tab</label>
                <select id="tabs" name="tabs" x-model="tab"
                    class="block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200">
                    <option value="banned-ips">Banned IPs</option>
                    <option value="jails">Jails</option>
                    <option value="ban-ip">Ban / Whitelist IP</option>
                </select>
            </div>

            <!-- Desktop Tabs -->
            <div class="hidden sm:block">
                <div class="border-b-2 border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px space-x-6" aria-label="Tabs">
                        <button @click="tab = 'banned-ips'"
                            :class="tab === 'banned-ips' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'banned-ips' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Banned IPs
                        </button>
                        <button @click="tab = 'jails'"
                            :class="tab === 'jails' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'jails' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Jails
                        </button>
                        <button @click="tab = 'ban-ip'"
                            :class="tab === 'ban-ip' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="group inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200">
                            <svg class="w-5 h-5"
                                :class="tab === 'ban-ip' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Ban / Whitelist IP
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div x-show="tab === 'banned-ips'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <livewire:server.fail2ban.iptables :server_id="$server_id" />
        </div>

        <div x-show="tab === 'jails'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
            <livewire:server.fail2ban.jails :server_id="$server_id" />
        </div>

        <div x-show="tab === 'ban-ip'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
            <livewire:server.fail2ban.ban-ip-form :server_id="$server_id" />
        </div>
    </div>
@endsection

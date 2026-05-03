@extends('layouts.app')

@section('title', __('spikster.titles.server') . ' — Fail2ban')

@section('content')
    <div class="space-y-6"
         x-data="{
             tab: 'banned-ips',
             deploying: false,
             deploySuccess: false,
             deployError: null,
             async deployApi() {
                 this.deploying = true;
                 this.deploySuccess = false;
                 this.deployError = null;
                 try {
                     const response = await fetch('/servers/{{ $server_id }}/fail2ban/deploy', {
                         method: 'POST',
                         headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
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

        <x-page-header title="Fail2ban Management">
            <x-slot name="actions">
                <x-back-button :href="route('server.edit', $server_id)" label="Back to Server" />
            </x-slot>
        </x-page-header>

        {{-- Deploy API Notice --}}
        <x-alert type="info">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-semibold">Fail2ban API Setup Required</p>
                    <p class="mt-1">The Fail2ban API must be deployed to your server before using these features.</p>

                    <div class="mt-3 flex items-center gap-4">
                        <x-primary-button @click="deployApi()" x-bind:disabled="deploying">
                            <svg x-show="deploying" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="deploying ? 'Deploying...' : 'Deploy Fail2ban API'"></span>
                        </x-primary-button>

                        <span x-show="deploySuccess" x-transition class="text-sm text-green-700 dark:text-green-300 font-medium flex items-center gap-1">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            API deployed successfully!
                        </span>
                        <span x-show="deployError" x-transition class="text-sm text-red-700 dark:text-red-300" x-text="deployError"></span>
                    </div>
                </div>
            </div>
        </x-alert>

        {{-- Tabs --}}
        <x-alpine-tabs model="tab" :tabs="[
            ['key' => 'banned-ips', 'label' => 'Banned IPs',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636\'/></svg>'],
            ['key' => 'jails', 'label' => 'Jails',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z\'/></svg>'],
            ['key' => 'ban-ip', 'label' => 'Ban / Whitelist IP',
                'icon' => '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\'/></svg>'],
        ]" />

        <div x-show="tab === 'banned-ips'" x-cloak>
            <livewire:server.fail2ban.iptables :server_id="$server_id" />
        </div>
        <div x-show="tab === 'jails'" x-cloak>
            <livewire:server.fail2ban.jails :server_id="$server_id" />
        </div>
        <div x-show="tab === 'ban-ip'" x-cloak>
            <livewire:server.fail2ban.ban-ip-form :server_id="$server_id" />
        </div>
    </div>
@endsection

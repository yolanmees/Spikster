@extends('layouts.app')

@section('title')
    {{ __('spikster.titles.server') }}
@endsection

@section('content')
    <div x-data="{ tab: 'monitor' }">
        <x-page-header title="{{ __('spikster.titles.server') }}">
            <x-slot name="actions">
                <x-back-button :href="route('server.list')" />
                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <span>IP: <b id="serveriptop" class="text-gray-900 dark:text-white"></b></span>
                    <span>{{ __('spikster.sites') }}: <b id="serversites" class="text-gray-900 dark:text-white"></b></span>
                    <span class="flex items-center gap-1">Ping: <span id="serverping">
                        <svg class="animate-spin h-4 w-4 inline-block text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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

        {{-- Monitor Tab --}}
        <div x-show="tab === 'monitor'" x-transition>
            <x-server-monitoring-charts :serverId="$server_id" />
        </div>

        {{-- Server Info Tab --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
            x-show="tab === 'server'" x-transition style="display: none;">
            <x-card header="{{ __('spikster.server_information') }}" size="md">
                <livewire:server.server-information :server_id="$server_id" />
            </x-card>
            <x-card header="{{ __('spikster.system_services') }}" size="md">
                <livewire:server.system-services :server_id="$server_id" />
            </x-card>
            <x-link-card
                title="Server Logs"
                description="Access and review system logs, error logs and access logs for debugging and monitoring."
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'
                iconColor="text-gray-600 dark:text-gray-400"
                :href="route('logs.index', ['server_id' => $server_id])"
                label="Open Logs"
                variant="light"
            />
        </div>

        {{-- Security Tab --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
            x-show="tab === 'security'" x-transition style="display: none;">

            <x-card header="Security & Protection" size="md">
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Fail2ban</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Monitor and manage blocked IP addresses to protect your server from brute-force attacks.</p>
                            <x-action-button :href="route('server.fail2ban', $server_id)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Open Fail2ban
                            </x-action-button>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card header="Server Health" size="md">
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Active Protection</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Firewall & monitoring enabled</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @foreach ([
                            ['SSH Protection',  'bg-blue-50 dark:bg-gray-800/50',   'text-blue-500',   'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',                         'Active'],
                            ['Firewall Rules',  'bg-gray-50 dark:bg-gray-800/50',   'text-purple-500', 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                       'Configured'],
                            ['Auto Updates',    'bg-gray-50 dark:bg-gray-800/50',   'text-orange-500', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'Enabled'],
                        ] as [$label, $bg, $iconColor, $path, $status])
                            <div class="flex items-center justify-between p-3 rounded-lg {{ $bg }}">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </div>
                                <x-badge color="green" :text="$status" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Tools Tab --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6"
            x-show="tab === 'tools'" x-transition style="display: none;">

            {{-- PHP CLI Version --}}
            <x-card header="PHP Configuration" size="md">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2">
                            {{ __('spikster.php_cli_version') }}
                        </label>
                        <div class="flex gap-2">
                            <select id="phpver" class="flex-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="8.3" id="php83">PHP 8.3</option>
                                <option value="8.2" id="php82">PHP 8.2</option>
                                <option value="8.1" id="php81">PHP 8.1</option>
                                <option value="8.0" id="php80">PHP 8.0</option>
                                <option value="7.4" id="php74">PHP 7.4</option>
                            </select>
                            <x-button id="changephp" variant="info" :outline="true" class="shrink-0 px-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </x-button>
                        </div>
                    </div>
                    <x-alert type="info">This changes the default PHP version for CLI commands.</x-alert>
                </div>
            </x-card>

            {{-- Cron Jobs --}}
            <x-card header="Scheduled Tasks" size="md">
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('spikster.manage_cron_jobs') }}</p>
                    <a href="{{ route('server.cron', $server_id) }}" class="block">
                        <x-primary-button type="button" class="w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('spikster.edit_crontab') }}
                        </x-primary-button>
                    </a>
                    <x-alert type="warning">Be careful when editing cron jobs. Incorrect syntax may break scheduled tasks.</x-alert>
                </div>
            </x-card>

            {{-- Password Reset --}}
            <x-card header="System Access" size="md">
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('spikster.reset_cipi_password') }}</p>
                    <x-danger-button type="button" id="rootreset" class="w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        {{ __('spikster.require_reset_cipi_password') }}
                    </x-danger-button>
                    <x-alert type="error">This will generate a new password for the server user. Store it safely.</x-alert>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@section('extra')
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

@section('js')
    <script>
        // Crontab editor (used for editcrontab via cron page route)
        Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#292b2c';

        function serverInit() {
            getDataNoDT('/api/servers', false);
            $.ajax({
                url: '/api/servers/{{ $server_id }}',
                type: 'GET',
                success: function(data) {
                    $('#serveriptop').html(data.ip);
                    $('#serversites').html(data.sites);
                    $('#maintitle').html('- ' + data.name);
                    $('#serverbuild').html(data.build || '{{ __('spikster.unknown') }}');
                    switch (data.php) {
                        case '8.3': $('#php83').attr("selected","selected"); break;
                        case '8.2': $('#php82').attr("selected","selected"); break;
                        case '8.1': $('#php81').attr("selected","selected"); break;
                        case '8.0': $('#php80').attr("selected","selected"); break;
                        case '7.4': $('#php74').attr("selected","selected"); break;
                        case '7.3': $('#phpver').append('<option value="7.3" selected>7.3</option>'); break;
                    }
                },
            });
        }
        serverInit();

        function getPing() {
            $.ajax({
                url: '/api/servers/{{ $server_id }}/ping',
                type: 'GET',
                timeout: 10000,
                beforeSend: function() {
                    $('#serverping').html('<svg class="animate-spin h-4 w-4 inline-block text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');
                },
                success: function(data) {
                    var icon = data.status === 'online'
                        ? '<svg class="w-4 h-4 inline-block text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>'
                        : '<svg class="w-4 h-4 inline-block text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
                    $('#serverping').html(icon);
                },
                error: function() {
                    $('#serverping').html('<svg class="w-4 h-4 inline-block text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>');
                }
            });
        }
        setInterval(getPing, 10000);
        getPing();

        // Change PHP CLI
        $('#changephp').click(function() {
            $.ajax({
                url: '/api/servers/{{ $server_id }}', type: 'PATCH',
                contentType: 'application/json', dataType: 'json',
                data: JSON.stringify({ 'php': $('#phpver').val() }),
                success: function() { serverInit(); },
            });
        });

        // Root Reset
        $('#rootreset').click(function() {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'root-reset-modal' }));
        });
        $('#rootresetsubmit').click(function() {
            $.ajax({
                url: '/api/servers/{{ $server_id }}/rootreset', type: 'POST',
                success: function(data) {
                    success('{{ __('spikster.new_password_success') }}:<br><b>' + data.password + '</b>');
                    $(window).scrollTop(0);
                    window.dispatchEvent(new CustomEvent('close-modal'));
                }
            });
        });
    </script>
@endsection

@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.server') }}
@endsection



@section('content')
<div x-data="{ tab: 'monitor' }">
    <ol class="breadcrumbs">
        <li class="breadcrumb-item active">IP:<b><span class="ml-1" id="serveriptop"></span></b></li>
        <li class="breadcrumb-item active">{{ __('spikster.sites') }}:<b><span class="ml-1" id="serversites"></span></b></li>
        <li class="breadcrumb-item active">Ping:<b><span class="ml-1" id="serverping"><i class="fas fa-circle-notch fa-spin"></i></span></b></li>
    </ol>

    <div class="pb-4">
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Select a tab</label>
            <select id="tabs" name="tabs" x-model="tab" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                <option value="monitor">Monitor</option>
                <option value="server">Server information</option>
                <option value="security">Security</option>
                <option value="tools">Tools</option>
            </select>
        </div>
        <div class="hidden sm:block">
            <nav class="flex space-x-4 border-b border-gray-200 dark:border-gray-700" aria-label="Tabs">
                <button 
                    @click="tab = 'monitor'" 
                    :class="tab === 'monitor' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Monitor
                </button>
                <button 
                    @click="tab = 'server'" 
                    :class="tab === 'server' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Server information
                </button>
                <button 
                    @click="tab = 'security'" 
                    :class="tab === 'security' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Security
                </button>
                <button 
                    @click="tab = 'tools'" 
                    :class="tab === 'tools' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Tools
                </button>
            </nav>
        </div>
    </div>



    <div class="grid grid-cols-2 gap-4" x-show="tab === 'monitor'" x-transition>
            @livewire('stats.cpu', ['server_id' => $server_id])
            @livewire('stats.mem', ['server_id' => $server_id])
            @livewire('stats.load', ['server_id' => $server_id])
            @livewire('stats.disk', ['server_id' => $server_id])
    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" x-show="tab === 'server'" x-transition style="display: none;">
        <x-card header="{{ __('spikster.server_information') }}" size="md" dark="false">
            {{-- <canvas id="cpuChart" width="100%" height="40"></canvas> --}}
            <x-input type="text" label="{{ __('spikster.server_name') }}:" placeholder="e.g. Production" id="servername" autocomplete="off" />
            <x-input type="text" label="{{ __('spikster.server_ip') }}:" placeholder="e.g. 123.123.123.123" id="serverip" autocomplete="off" />
            <x-input type="text" label="{{ __('spikster.server_provider') }}:" placeholder="e.g. Digital Ocean" id="serverprovider" autocomplete="off" />
            <x-input type="text" label="{{ __('spikster.server_location') }}:" placeholder="e.g. Amsterdam" id="serverlocation" autocomplete="off" />
            <x-button type="button" id="updateServer">{{ __('spikster.update') }}</x-button>
        </x-card>
        <x-card header="{{ __('spikster.system_services') }}" size="md" dark="false">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="flex justify-between gap-4">
                    <p>nginx</p>
                    <x-button type="button" variant="warning" id="restartnginx">{{ __('spikster.restart') }} </x-button>
                </div>
                <div class="flex justify-between gap-4">
                    <p>PHP-FPM</p>
                    <x-button type="button" variant="warning" id="restartphp">{{ __('spikster.restart') }} </x-button>
                </div>
                <div class="flex justify-between gap-4">
                    <p>MySql</p>
                    <x-button type="button" variant="warning" id="restartmysql">{{ __('spikster.restart') }}</x-button>
                </div>
                <div class="flex justify-between gap-4">
                    <p>Redis</p>
                    <x-button type="button" variant="warning" id="restartredis">{{ __('spikster.restart') }} </x-button>
                </div>
                <div class="flex justify-between gap-4">
                    <p>Supervisor</p>
                    <x-button type="button" variant="warning" id="restartsupervisor">{{ __('spikster.restart') }} </x-button>
                </div>
            </div>
        </x-card>
        <x-card header="Logs" size="md" dark="false">
            <a href="{{route('logs', $server_id)}}">
                <x-button>
                    Open Logs
                </x-button>
            </a>
        </x-card>
    </div>
    <div class="flex gap-x-4" x-show="tab === 'security'" x-transition style="display: none;">
        <div class="w-1/2">
            <x-card header="Security" size="md" dark="false">
                <p class="mb-4">Fail2ban</p>
                <div>
                    <x-action-button :href="route('server.fail2ban', $server_id)">
                        Open Fail2ban
                    </x-action-button>
                </div>
            </x-card>
        </div>
    </div>
    <div class="flex gap-x-4" x-show="tab === 'tools'" x-transition style="display: none;">
        <div class="w-1/3">
            <x-card header="{{ __('spikster.tools') }}" size="md" dark="false">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('spikster.php_cli_version') }}:
                    </label>
                    <div class="flex gap-2">
                        <select id="phpver" class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                            <option value="8.3" id="php83">8.3</option>
                            <option value="8.2" id="php82">8.2</option>
                            <option value="8.1" id="php81">8.1</option>
                            <option value="8.0" id="php80">8.0</option>
                            <option value="7.4" id="php74">7.4</option>
                        </select>
                        <button type="button" id="changephp" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <x-icon icon="edit" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('spikster.manage_cron_jobs') }}:
                    </p>
                    <x-primary-button type="button" id="editcrontab">
                        {{ __('spikster.edit_crontab') }}
                    </x-primary-button>
                </div>
                
                <div class="mb-4">
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('spikster.reset_cipi_password') }}:
                    </p>
                    <x-danger-button type="button" id="rootreset">
                        {{ __('spikster.require_reset_cipi_password') }}
                    </x-danger-button>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection



@section('extra')
<input type="hidden" id="currentip">

<!-- Update Server Modal -->
<div x-data="{ showUpdateModal: false }" x-show="showUpdateModal" x-cloak id="updateServerModalContainer" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showUpdateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUpdateModal = false"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div x-show="showUpdateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('spikster.update_server_modal_title') }}
                    </h3>
                    <button type="button" @click="showUpdateModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <x-icon icon="x" class="h-6 w-6" />
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('spikster.update_server_modal_text') }}</p>
                    <p class="hidden mt-2 text-sm font-bold text-gray-900 dark:text-white" id="ipnotice">{!! __('spikster.update_server_modal_ip') !!}</p>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <x-primary-button type="button" id="submit" class="sm:ml-3">
                    {{ __('spikster.confirm') }}
                </x-primary-button>
                <x-secondary-button type="button" @click="showUpdateModal = false">
                    Cancel
                </x-secondary-button>
            </div>
        </div>
    </div>
</div>

<!-- Crontab Modal -->
<div x-data="{ showCrontabModal: false }" x-show="showCrontabModal" x-cloak id="crontabModalContainer" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showCrontabModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCrontabModal = false"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div x-show="showCrontabModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('spikster.server_crontab') }}
                    </h3>
                    <button type="button" @click="showCrontabModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <x-icon icon="x" class="h-6 w-6" />
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ __('spikster.server_crontab_edit') }}:</p>
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
<div x-data="{ showRootResetModal: false }" x-show="showRootResetModal" x-cloak id="rootresetModalContainer" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showRootResetModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRootResetModal = false"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div x-show="showRootResetModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('spikster.require_password_reset_modal_title') }}
                    </h3>
                    <button type="button" @click="showRootResetModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <x-icon icon="x" class="h-6 w-6" />
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('spikster.require_password_reset_modal_text') }}</p>
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
        Alpine.store('modals', { showCrontabModal: true });
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
        getDataNoDT('/api/servers',false);
        $.ajax({
            url: '/api/servers/{{ $server_id }}',
            type: 'GET',
            success: function(data) {
                $('#mainloading').addClass('d-none');
                $('#serveriptop').html(data.ip);
                $('#serversites').html(data.sites);
                $('#maintitle').html('- '+data.name);
                $('#servername').val(data.name);
                $('#serverip').val(data.ip);
                $('#serverprovider').val(data.provider);
                $('#serverlocation').val(data.location);
                $('#currentip').val(data.ip);
                crontab.session.setValue(data.cron);
                $('#serverbuild').empty();
                if(data.build) {
                    $('#serverbuild').html(data.build);
                } else {
                    $('#serverbuild').html('{{ __('spikster.unknown') }}');
                }
                switch (data.php) {
                    case '8.3':
                        $('#php83').attr("selected","selected");
                        break;
                    case '8.2':
                        $('#php82').attr("selected","selected");
                        break;
                    case '8.1':
                        $('#php81').attr("selected","selected");
                        break;
                    case '8.0':
                        $('#php80').attr("selected","selected");
                        break;
                    case '7.4':
                        $('#php74').attr("selected","selected");
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
                $('#serverping').html('<i class="fas fa-circle-notch fa-spin" title="{{ __('spikster.loading_data') }}"></i>');
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
                $('#changephp').html('<i class="fas fa-circle-notch fa-spin" title="{{ __('spikster.loading_please_wait') }}"></i>');
            },
            success: function(data) {
                $('#changephp').empty();
                $('#changephp').html('<i class="fas fas fa-edit"></i>');
            },
        });
        serverInit();
    });

    // Restart nginx
    $('#restartnginx').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/nginx',
            type: 'POST',
            beforeSend: function() {
                $('#loadingnginx').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingnginx').addClass('d-none');
            },
        });
    });

    // Restart php
    $('#restartphp').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/php',
            type: 'POST',
            beforeSend: function() {
                $('#loadingphp').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingphp').addClass('d-none');
            },
        });
    });

    // Restart mysql
    $('#restartmysql').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/mysql',
            type: 'POST',
            beforeSend: function() {
                $('#loadingmysql').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingmysql').addClass('d-none');
            },
        });
    });

    // Restart redis
    $('#restartredis').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/redis',
            type: 'POST',
            beforeSend: function() {
                $('#loadingredis').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingredis').addClass('d-none');
            },
        });
    });

    // Restart supervisor
    $('#restartsupervisor').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}/servicerestart/supervisor',
            type: 'POST',
            beforeSend: function() {
                $('#loadingsupervisor').removeClass('d-none');
            },
            success: function(data) {
                $('#loadingsupervisor').addClass('d-none');
            },
        });
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
                success('{{ __('spikster.new_password_success') }}:<br><b>'+data.password+'</b>');
                $(window).scrollTop(0);
                document.querySelector('#rootresetModalContainer').__x.$data.showRootResetModal = false;
            }
        });
    });

    //Check IP conflict (edit)
    function ipConflictEdit(ip,server_id) {
        conflict = 0;
        JSON.parse(localStorage.otherdata).forEach(server => {
            if(ip === server.ip && server.server_id !== server_id) {
                conflict = conflict + 1;
            }
        });
        return conflict;
    }

    // Update Server
    $('#updateServer').click(function() {
        $('#ipnotice').addClass('d-none');
        if($('#serverip').val() != $('#currentip').val()) {
            $('#newip').html($('#serverip').val());
            $('#ipnotice').removeClass('d-none');
        }
        validation = true;
        if(!$('#servername').val() || $('#servername').val().length < 3) {
            $('#servername').addClass('is-invalid');
            $('#submit').addClass('disabled');
            validation = false;
        }
        server_id = '{{ $server_id }}';
        if(!$('#serverip').val() || !ipValidate($('#serverip').val()) || ipConflictEdit($('#serverip').val(),server_id) > 0) {
            $('#serverip').addClass('is-invalid');
            $('#submit').addClass('disabled');
            validation = false;
        }
        if(validation) {
            $('#loading').addClass('d-none');
            document.querySelector('#updateServerModalContainer').__x.$data.showUpdateModal = true;
        }
    });

    // Update Server Validation
    $('#servername').keyup(function() {
        $('#servername').removeClass('is-invalid');
        $('#submit').removeClass('disabled');
    });
    $('#serverip').keyup(function() {
        $('#serverip').removeClass('is-invalid');
        $('#submit').removeClass('disabled');
    });

    // Update Server Submit
    $('#submit').click(function() {
        $.ajax({
            url: '/api/servers/{{ $server_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'name':     $('#servername').val(),
                'ip':       $('#serverip').val(),
                'provider': $('#serverprovider').val(),
                'location': $('#serverlocation').val()
            }),
            beforeSend: function() {
                $('#loading').removeClass('d-none');
            },
            success: function(data) {
                serverInit();
                $('#loading').addClass('d-none');
            },
            complete: function() {
                $('#ipnotice').addClass('d-none');
                document.querySelector('#updateServerModalContainer').__x.$data.showUpdateModal = false;
            }
        });
    });

    // Charts style
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';
</script>
@endsection

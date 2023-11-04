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
            <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
            <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option selected>Monitor</option>
                <option>Server information</option>
                <option>Security</option>
                <option>Tools</option>
            </select>
        </div>
        <div class="hidden sm:block">
            <nav class="flex space-x-4" aria-label="Tabs">
                <!-- Current: "bg-gray-200 text-gray-800", Default: "text-gray-600 hover:text-gray-800" -->
                <a @click="tab = 'monitor'" clas="tab" :class="tab === 'monitor' ? 'tab-item-active' : 'tab-item'" aria-current="page">Monitor</a>
                <a @click="tab = 'server'" :class="tab === 'server' ? 'tab-item-active' : 'tab-item'">Server information</a>
                <a @click="tab = 'security'" :class="tab === 'security' ? 'tab-item-active' : 'tab-item'">Security</a>
                <a @click="tab = 'tools'" :class="tab === 'tools' ? 'tab-item-active' : 'tab-item'">Tools</a>
            </nav>
        </div>
    </div>



    <div class="grid grid-cols-2 gap-x-4" x-show="tab === 'monitor'">
        <div class="">
            @livewire('stats.cpu', ['server_id' => $server_id])
        </div>
        <div class="">
            @livewire('stats.mem', ['server_id' => $server_id])
        </div>
        <div class="">
            @livewire('stats.load', ['server_id' => $server_id])
        </div>
        <div class="">
            @livewire('stats.disk', ['server_id' => $server_id])
        </div>

    </div>


    <div class="flex gap-x-4" x-show="tab === 'server'">
        <div class="w-1/3">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle fs-fw mr-1"></i>
                    {{ __('spikster.server_information') }}
                </div>
                <div class="card-body">
                    <p>{{ __('spikster.server_name') }}:</p>
                    <div class="input-group">
                        <input class="form-control" type="text" placeholder="e.g. Production" id="servername" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <p>{{ __('spikster.server_ip') }}:</p>
                    <div class="input-group">
                        <input class="form-control" type="text" placeholder="e.g. 123.123.123.123" id="serverip" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <p>{{ __('spikster.server_provider') }}:</p>
                    <div class="input-group">
                        <input class="form-control" type="text" placeholder="e.g. Digital Ocean" id="serverprovider" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <p>{{ __('spikster.server_location') }}:</p>
                    <div class="input-group">
                        <input class="form-control" type="text" placeholder="e.g. Amsterdam" id="serverlocation" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <div class="text-center">
                        <button class="btn btn-primary" type="button" id="updateServer">{{ __('spikster.update') }}</button>
                    </div>
                    <div class="space"></div>
                    <div class="space"></div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
        <div class="w-1/3">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-power-off fs-fw mr-1"></i>
                    {{ __('spikster.system_services') }}
                </div>
                <div class="card-body">
                    <p>nginx</p>
                    <div class="text-center">
                        <button class="btn btn-warning" type="button" id="restartnginx">{{ __('spikster.restart') }} </button>
                    </div>
                    <div class="space"></div>
                    <p>PHP-FPM</p>
                    <div class="text-center">
                        <button class="btn btn-warning" type="button" id="restartphp">{{ __('spikster.restart') }} </button>
                    </div>
                    <div class="space"></div>
                    <p>MySql</p>
                    <div class="text-center">
                        <button class="btn btn-warning" type="button" id="restartmysql">{{ __('spikster.restart') }}</button>
                    </div>
                    <div class="space"></div>
                    <p>Redis</p>
                    <div class="text-center">
                        <button class="btn btn-warning" type="button" id="restartredis">{{ __('spikster.restart') }} </button>
                    </div>
                    <div class="space"></div>
                    <p>Supervisor</p>
                    <div class="text-center">
                        <button class="btn btn-warning" type="button" id="restartsupervisor">{{ __('spikster.restart') }} </button>
                    </div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex gap-x-4" x-show="tab === 'security'">
        <div class="w-1/2">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shield-alt fs-fw mr-1"></i>
                    Security
                </div>
                <div class="card-body">
                    <p>Fail2ban</p>
                    <div>
                        <a href="{{route('server.fail2ban', $server_id)}}" class="btn btn-primary" type="button" id="">Open Fail2ban</a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <div class="flex gap-x-4" x-show="tab === 'tools'">
        <div class="w-1/3">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools fs-fw mr-1"></i>
                    {{ __('spikster.tools') }}
                </div>
                <div class="card-body">
                    <p>{{ __('spikster.php_cli_version') }}:</p>
                    <div class="input-group">
                        <select class="form-control" id="phpver">
                            <option value="8.2" id="php82">8.2</option>
                            <option value="8.1" id="php81">8.1</option>
                            <option value="8.0" id="php80">8.0</option>
                            <option value="7.4" id="php74">7.4</option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="changephp"><i class="fas fa-edit"></i></button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="mb-2">{{ __('spikster.manage_cron_jobs') }}:</p>
                        <button class="btn btn-primary" type="button" id="editcrontab">{{ __('spikster.edit_crontab') }}</button>
                    </div>
                    <div class="mt-4">
                        <p class="mb-2">{{ __('spikster.reset_cipi_password') }}:</p>
                        <button class="btn btn-danger" type="button" id="rootreset">{{ __('spikster.require_reset_cipi_password') }}</button>

                    </div>
                    <div class="mt-4">
                        <p class="mb-2">{{ __('spikster.cipi_build_version') }}:</p>
                        <span class="btn btn-secondary" id="serverbuild"></span>
                    </div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
       

    </div>
    @endsection



    @section('extra')
    <input type="hidden" id="currentip">
    <dialog class="modal fade" id="updateServerModal" tabindex="-1" role="dialog" aria-labelledby="updateServerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" id="updateserverdialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateServerModalLabel">{{ __('spikster.update_server_modal_title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ __('spikster.update_server_modal_text') }}</p>
                    <p class="d-none" id="ipnotice"><b>{!! __('spikster.update_server_modal_ip') !!}</b></p>
                    <div class="text-center">
                        <button class="btn btn-primary" type="button" id="submit">{{ __('spikster.confirm') }} </button>
                    </div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
    </dialog>
    <dialog class="modal fade" id="crontabModal" tabindex="-1" role="dialog" aria-labelledby="crontabModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crontabModalLabel">{{ __('spikster.server_crontab') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ __('spikster.server_crontab_edit') }}:</p>
                    <div id="crontab" style="height:250px;width:100%;"></div>
                    <div class="space"></div>
                    <div class="text-center">
                        <button class="btn btn-primary" type="button" id="crontabsubmit">{{ __('spikster.save') }} <i class="fas fa-circle-notch fa-spin d-none" id="crontableloading"></i></button>
                    </div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
    </dialog>
    <dialog class="modal fade" id="rootresetModal" tabindex="-1" role="dialog" aria-labelledby="rootresetModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rootresetModalLabel">{{ __('spikster.require_password_reset_modal_title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ __('spikster.require_password_reset_modal_text') }}</p>
                    <div class="space"></div>
                    <div class="text-center">
                        <button class="btn btn-danger" type="button" id="rootresetsubmit">{{ __('spikster.confirm') }} <i class="fas fa-circle-notch fa-spin d-none" id="rootresetloading"></i></button>
                    </div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
    </dialog>
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
        $('#crontabModal').modal();
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
            beforeSend: function() {
                $('#crontableloading').removeClass('d-none');
            },
            success: function(data) {
                $('#crontableloading').addClass('d-none');
                $('#crontabModal').modal('toggle');
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
        $('#rootresetModal').modal();
    });

    // Root Reset Submit
    $('#rootresetsubmit').click(function() {
        $('#rootresetloading').removeClass('d-none');
        $.ajax({
            url: '/api/servers/{{ $server_id }}/rootreset',
            type: 'POST',
            success: function(data) {
                success('{{ __('spikster.new_password_success') }}:<br><b>'+data.password+'</b>');
                $(window).scrollTop(0);
                $('#rootresetModal').modal('toggle');
            },
            complete: function() {
                $('#rootresetloading').addClass('d-none');
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
            $('#updateServerModal').modal();
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
                $('#updateServerModal').modal('toggle');
            }
        });
    });

    // Charts style
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';
</script>
@endsection

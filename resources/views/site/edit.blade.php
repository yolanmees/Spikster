@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.site') }}
@endsection



@section('content')
<ol class="breadcrumbs mb-4">
    <li class="breadcrumb-item active">IP:<b><span class="ml-1" id="siteip"></span></b></li>
    <li class="breadcrumb-item active">{{ __('spikster.aliases') }}:<b><span class="ml-1" id="sitealiases"></span></b></li>
    <li class="breadcrumb-item active">PHP:<b><span class="ml-1" id="sitephp"></span></b></li>
    <li class="breadcrumb-item active">{{ __('spikster.site_base_path') }}:<b><span class="ml-1">/home/</span><span id="siteuserinfo"></span>/web/<span id="sitebasepathinfo"></span></b></li>
</ol>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <x-card header="{{ __('spikster.basic_information') }}" size="md" dark="false">
            <x-input label="{{ __('spikster.domain') }}:" type="text" placeholder="e.g. domain.ltd" id="sitedomain" autocomplete="off" />
            <x-input label="{{ __('spikster.site_base_path') }}:" type="text" placeholder="e.g. public" id="sitebasepath" autocomplete="off" />
            <x-button class="btn btn-primary" type="button" id="updateSite">{{ __('spikster.update') }}</x-button>
    </x-card>
    <x-card header="{{ __('spikster.manage_aliases') }}" size="md" dark="false">
        <p class="mb-2">{{ __('spikster.add_alias') }}:</p>
        <div class="input-group">
            <input class="form-control" type="text" placeholder="e.g. www.domain.ltd" id="siteaddalias" autocomplete="off" />
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="siteaddaliassubmit"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div>
            <p class="mb-2">{{ __('spikster.aliases') }}:</p>
            <div id="sitealiaseslist"></div>
        </div>
    </x-card>
    <x-card header="{!! __('spikster.ssl_security') !!}" size="md" dark="false">
        <p class="mb-2">{{ __('spikster.ssl_security_text') }}:</p>
        <div class="flex gap-4 mb-4">
            <x-button type="button" id="sitessl">{{ __('spikster.ssl_generate') }}</x-button>
        </div>
        <p class="mb-2">{{ __('spikster.password_resets') }}:</p>
        <div class="flex gap-4">
            <x-button type="button" id="sitesshreset">SSH</x-button>
            <x-button type="button" id="sitemysqlreset">MySql</x-button>
        </div>
    </x-card>
    <x-card header="{{ __('spikster.github_repository') }}" size="md" dark="false">
        <p class="mb-2">{{ __('spikster.github_repository_setup') }}</p>
        <div class="flex gap-4 mb-4">
            <x-button type="button" style="min-width:200px" id="sitesetrepo">{{ __('spikster.github_repository_config') }}</x-button>
            <x-button type="button" style="min-width:200px" id="editdeploy">{{ __('spikster.github_repository_scripts') }}</x-button>
        </div>
        <p class="mb-2">{{ __('spikster.github_repository_deploy') }}:</p>
        <div class="border p-2">
            <code>
                ssh <span id="repodeployinfouser1"></span>@<span id="repodeployinfoip"></span> <br/>
                sh /home/<span id="repodeployinfouser2"></span>/git/deploy.sh
            </code>
        </div>
    </x-card>
    <x-card header="{{ __('spikster.tools') }}" size="md" dark="false">
        <p class="mb-2">{{ __('spikster.php_fpm_version') }}:</p>
        <div class="input-group">
            <select class="form-control" id="sitephpver">
                <option value="8.3" id="php83">8.3</option>
                <option value="8.2" id="php82">8.2</option>
                <option value="8.1" id="php81">8.1</option>
                <option value="8.0" id="php80">8.0</option>
                <option value="7.4" id="php74">7.4</option>
            </select>
            <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="sitephpversubmit"><i class="fas fa-edit"></i></button>
            </div>
        </div>
        <p class="mb-2">Supervisor script:</p>
        <x-input class="form-control" type="text" id="sitesupervisor" autocomplete="off" />
        <x-button class="btn btn-primary" type="button" id="sitesupervisorupdate">{{ __('spikster.update') }}</x-button>
    </x-card>
    <x-card header="MYSQL" size="md" dark="false">
        <p class="mb-2">Set up your database</p>
        <a href="{{ route('site.database', $site_id) }}"> 
            <x-button class="btn btn-primary" type="button"  id="sitesetrepo"> DATABASE </x-button>
        </a>
    </x-card>
    <x-card header="Wordpress Manager" size="md" dark="false">
        <p class="mb-2">Manage your project</p>
        <a href="{{ route('site.wordpress', $site_id) }}"> 
            <x-button class="btn btn-primary" type="button" id="sitesetrepo"> Wordpress Manager </x-button>
        </a>
    </x-card>

    {{-- <div class="md:col-span-2 col-span-1">
        <div class="card h-full">
            <div class="card-header">
                <i class="fas fa-rocket fs-fw mr-1"></i>
                File Manager
            </div>
            <div class="card-body text-center">
                <div class="space"></div>
                <form action="{{route('files.index')}}" method="get">
                    <input type="hidden" name="site-uuid" id="siteuuid">
                    <input type="submit" value="Open Manager" class="btn btn-primary">
                </form>
                <div class="space"></div>
            </div>
        </div>
    </div> --}}

    {{-- <div id="nodejsManager" class="md:col-span-2 col-span-1 d-none">
        <div class="card h-full">
            <div class="card-header">
                <i class="fab fa-github fs-fw mr-1"></i>
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
<dialog class="modal fade" id="repositoryModal" tabindex="-1" role="dialog" aria-labelledby="repositoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="repositorydialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repositoryModalLabel">{{ __('spikster.github_repository') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="repositoryproject">{{ __('spikster.repository_project') }}</label>
                <div class="input-group">
                    <input class="form-control" type="text" id="repositoryproject" placeholder="e.g. johndoe/helloworld" autocomplete="off" />
                </div>
                <div class="space"></div>
                <label for="repositorybranch">{{ __('spikster.repository_branch') }}</label>
                <div class="input-group">
                    <input class="form-control" type="text" id="repositorybranch" placeholder="e.g. develop" autocomplete="off" />
                </div>
                <div class="space"></div>
                <label for="deploykey">{{ __('spikster.repository_deploy_key') }} {!! __('spikster.repository_deploy_key_info') !!}</label>
                <div class="input-group">
                    <textarea id="deploykey" readonly style="width:100%;height:150px;font-size:10px;"></textarea>
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="repositorysubmit">{{ __('spikster.confirm') }} </button>
                </div>
            </div>
        </div>
    </div>
</dialog>
<dialog class="modal fade" id="deployModal" tabindex="-1" role="dialog" aria-labelledby="deployModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deployModalLabel">{{ __('spikster.deploy_scripts') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">{{ __('spikster.github_repository_scripts') }}:</p>
                <div id="deploy" style="height:250px;width:100%;"></div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="deploysubmit">{{ __('spikster.save') }} </button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</dialog>
<dialog class="modal fade" id="sshresetModal" tabindex="-1" role="dialog" aria-labelledby="sshresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sshresetModalLabel">{{ __('spikster.require_password_reset_modal_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">{{ __('spikster.require_ssh_password_reset_modal_text') }}</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="sshresetsubmit">{{ __('spikster.confirm') }}</button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</dialog>
<dialog class="modal fade" id="mysqlresetModal" tabindex="-1" role="dialog" aria-labelledby="mysqlresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mysqlresetModalLabel">{{ __('spikster.require_password_reset_modal_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">{{ __('spikster.require_mysql_password_reset_modal_text') }}</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="mysqlresetsubmit">{{ __('spikster.confirm') }} </button>
                </div>
                <div class="space"></div>
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
                $('#maintitle').html('- '+data.domain);
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
                getDataNoDT('/api/servers/'+data.server_id+'/domains');
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
        $.ajax({
            url: '/api/sites/{{ $site_id }}/aliases',
            type: 'GET',
            success: function(data) {
                $('#sitealiaseslist').empty();
                jQuery(data).each(function(i, item){
                    $('#sitealiaseslist').append('<span class="badge badge-info mr-2 ml-2">'+item.domain+'<i data-id="'+item.alias_id+'" style="cursor:pointer" class="sitealiasdel fas fa-times fs-fw ml-2"></i></span>');
                });
            },
        });
    }
    $(document).ajaxSuccess(function(){
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
                success('{{ __('spikster.new_ssh_password_success') }}:<br><b>'+data.password+'</b><br><a href="'+data.pdf+'" target="_blank" style="color:#ffffff">{{ __('spikster.download_site_data') }}</a>');
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
                success('{{ __('spikster.new_mysql_password_success') }}:<br><b>'+data.password+'</b><br><a href="'+data.pdf+'" target="_blank" style="color:#ffffff">{{ __('spikster.download_site_data') }}</a>');
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
    $("#copykey").click(function(){
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
            if(item == domain) {
                conflict = conflict + 1;
            }
        });
        return conflict;
    }


    // Site Aliases
    $('#siteaddalias').keyup(function() {
        $('#siteaddalias').removeClass('is-invalid');
    });
    $('#siteaddaliassubmit').click(function() {
        if(domainConflict($('#siteaddalias').val()) < 1 && $('#siteaddalias').val() != '') {
            $.ajax({
                url: '/api/sites/{{ $site_id }}/aliases',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'domain': $('#siteaddalias').val(),
                }),
                beforeSend: function() {
                    $('#siteaddaliassubmit').html('<i class="fas fa-circle-notch fa-spin"></i>');
                },
                success: function(data) {
                    $('#siteaddalias').val('');
                    $('#siteaddaliassubmit').empty();
                    $('#siteaddaliassubmit').html('<i class="fas fas fa-edit"></i>');
                    siteInit();
                },
            });
        } else {
            $('#siteaddalias').addClass('is-invalid');
        }
    });

    //Delete Aliases
    function aliasesDelete() {
        $(".sitealiasdel").on("click", function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}/aliases/'+$(this).attr('data-id'),
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
                $('#sitephpversubmit').html('<i class="fas fa-circle-notch fa-spin"></i>');
            },
            success: function(data) {
                $('#sitephpversubmit').empty();
                $('#sitephpversubmit').html('<i class="fas fas fa-edit"></i>');
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

@extends('layouts.app')


@section('title')
    {{ __('spikster.titles.dashboard') }}
@endsection



@section('content')
    <div class="space-y-6">
        <x-page-header title="{{ __('spikster.titles.dashboard') }}" />

        <x-flash-messages />

        {{-- Server overview (jQuery-driven, see @js below) --}}
        <div id="mainloading" class="flex justify-center py-12">
            <x-wire-spinner />
        </div>

        <div id="dashboard" class="space-y-4"></div>

        {{-- Livewire top-sites widget --}}
        <div class="mt-6">
            @livewire('dashboard.top-sites')
        </div>
    </div>
@endsection



@section('extra')
@endsection



@section('css')
@endsection



@section('js')
    <script>
        // Loading
        $('#mainloading').show();

        // Get Servers
        count = 0;
        $.ajax({
            type: 'GET',
            url: '/api/servers',
            success: function(data) {
                $('#mainloading').hide();
                data.forEach(server => {
                    if (server.status > 0) {
                        $.ajax({
                            type: 'GET',
                            url: '/api/servers/' + server.server_id + '/healthy',
                            beforeSend: function() {
                                $('#ram-' + server.server_id).html(
                                    '<svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                                    );
                                $('#cpu-' + server.server_id).html(
                                    '<svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                                    );
                                $('#hdd-' + server.server_id).html(
                                    '<svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                                    );
                            },
                            success: function(data) {
                                $('#ram-' + server.server_id).html(data.ram + '%');
                                $('#cpu-' + server.server_id).html(data.cpu + '%');
                                $('#hdd-' + server.server_id).html(data.hdd + '%');
                            }
                        });
                        $('#dashboard').append(`<div class="w-full servercard" serverid="` + server
                            .server_id + `">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all">
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 items-center">
                            <div class="col-span-2 md:col-span-1">
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1 hidden lg:block">{{ __('spikster.server') }}</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">` + server.name + `</div>
                            </div>
                            <div class="hidden xl:block">
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1 text-center">{{ __('spikster.sites') }}</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white text-center">` + server
                            .sites +
                            `</div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1 text-center">{{ __('spikster.cpu') }}</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white text-center" id="cpu-` + server
                            .server_id +
                            `"><svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1 text-center">{{ __('spikster.ram') }}</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white text-center" id="ram-` + server
                            .server_id +
                            `"><svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1 text-center">{{ __('spikster.hdd') }}</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white text-center" id="hdd-` + server
                            .server_id + `"><svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                            </div>
                            <div class="flex justify-end">
                                <a href="/servers/` + server.server_id +
                            `" title="{{ __('spikster.manage') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors" id="ping-container-` +
                            server.server_id + `">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" id="ping-` + server
                            .server_id + `" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>`);

                        count = count + 1;
                    }
                });
                if (count == 0) {
                    $('#dashboard').html(`
                        <x-empty-state
                            icon="server"
                            title="{{ __('spikster.no_results_found') }}"
                            message="{{ __('spikster.add_new_server') }}"
                        />
                    `);
                    // Fallback plain HTML for jQuery context
                    $('#dashboard').html('<div class="flex flex-col items-center justify-center py-12 text-center">' +
                        '<svg class="w-24 h-24 text-gray-300 dark:text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />' +
                        '</svg>' +
                        '<h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('spikster.no_results_found') }}</h4>' +
                        '<a href="/servers" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>' +
                        '{{ __('spikster.add_new_server') }}!' +
                        '</a></div>');
                }
            }
        });


        //Refresh Servers Status
        setInterval(function() {
            $('.servercard').each(function(server) {
                var thisserverping = $(this).attr('serverid');
                (function(thisserverping) {
                    $.ajax({
                        type: 'GET',
                        url: '/api/servers/' + thisserverping + '/ping',
                        beforeSend: function() {
                            $('#ping-' + thisserverping).html(
                                '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>'
                                );
                            $('#ping-' + thisserverping).parent().addClass('animate-spin');
                        },
                        success: function(data) {
                            $('#ping-' + thisserverping).parent().removeClass(
                                'animate-spin');
                            $('#ping-' + thisserverping).html(
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />'
                                );
                        }
                    });
                })(thisserverping);
            });
        }, 10000);

        //Refresh Health Stats
        setInterval(function() {
            $('.servercard').each(function(server) {
                var thisserverstatus = $(this).attr('serverid');
                (function(thisserverstatus) {
                    $.ajax({
                        type: 'GET',
                        url: '/api/servers/' + thisserverstatus + '/healthy',
                        beforeSend: function() {
                            $('#ram-' + thisserverstatus).html(
                                '<svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                                );
                            $('#cpu-' + thisserverstatus).html(
                                '<svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                                );
                            $('#hdd-' + thisserverstatus).html(
                                '<svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                                );
                        },
                        success: function(data) {
                            $('#ram-' + thisserverstatus).html(data.ram + '%');
                            $('#cpu-' + thisserverstatus).html(data.cpu + '%');
                            $('#hdd-' + thisserverstatus).html(data.hdd + '%');
                        }
                    });
                })(thisserverstatus);
            });
        }, 30000);
    </script>
@endsection

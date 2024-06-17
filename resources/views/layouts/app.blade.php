<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex">
    <title>{{ config('cipi.name') }} | @yield('title')</title>
    <meta name="cipi-version" content="{{ Storage::get('cipi/version.md') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    {{-- <link href="https://cdn.datatables.net/responsive/2.2.7/css/responsive.bootstrap4.min.css" rel="stylesheet" /> --}}
    <link rel="icon" type="image/png" href="/favicon.png" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="/css/app.css" rel="stylesheet" />
    
    <style>
        .space {
            min-height: 20px;
        }

        #mainloading {
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            position: fixed;
            display: block;
            opacity: 0.8;
            background-color: #000;
            z-index: 99;
            text-align: center;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
        }

        #mainloadingicon {
            margin: 0 auto;
            margin-top: 100px;
            z-index: 100000;
        }
    </style>
    @yield('css')
</head>
<body x-data="{ sidebarOpen: false, darkMode: false }"
      x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')) || false; $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
      x-bind:class="{ 'dark': darkMode }" class="bg-gray-100 dark:bg-gray-900 dark:text-white">
    <div>
        @include('layouts.components.mobile-sidebar')
        @include('layouts.components.sidebar')

        <div class="xl:pl-72">
            <!-- Sticky search header -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-6 border-b border-white/5 bg-gray-900 dark:bg-gray-800 px-4 shadow-sm sm:px-6 lg:px-8">
                <button x-on:click="sidebarOpen = true" type="button" class="-m-2.5 p-2.5 text-white xl:hidden">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10zm0 5.25a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                    </svg>
                </button>


                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <form class="flex flex-1" action="#" method="GET">
                        <label for="search-field" class="sr-only">Search</label>
                        <div class="relative w-full">
                            <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                            <input id="search-field" class="block h-full w-full border-0 bg-transparent py-0 pl-8 pr-0 text-white dark:bg-gray-800 dark:text-gray-300 focus:ring-0 sm:text-sm" placeholder="Search..." type="search" name="search">
                        </div>
                    </form>
                </div>
                
          
            </div>

            <main class="m-4">
                @yield('content')
            </main>
            <div class="grid place-items-center"> 
                @yield('extra')
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.7/js/responsive.bootstrap4.min.js"></script>
    <script>
        //Init Datatable Data
        localStorage.dtdata = '';

        //Datatable Render Check
        function dtRender() {
            if (document.getElementById('dt').innerHTML == 'OFF') {
                renderMake();
            } else {
                $('#dt').DataTable().clear().destroy();
                renderMake();
            }

        }

        //Vars in Page
        $('#username').html(localStorage.username);
        $('#panelversion').html($('meta[name="cipi-version"]').attr('content'));

        //Success notification
        function success(text) {
            $('#successtext').empty();
            $('#successtext').html(text);
            $('#success').removeClass('d-none');
        }

        //Fail notification
        function fail(text) {
            $('#failtext').empty();
            $('#failtext').html(text);
            $('#fail').removeClass('d-none');
        }

        //Success notification hide
        $('#successx').click(function() {
            $('#success').addClass('d-none');
            $('#successtext').empty();
        });

        //Fail notification hide
        $('#failx').click(function() {
            $('#fail').addClass('d-none');
            $('#failtext').empty();
        });

        //Tooltips
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })

        //IP Validation
        function ipValidate(ip) {
            return (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ip))
        }

        //jQuery AJAX Authorization Header Setup & Default Error
        $.ajaxSetup({
            cache: false
            , headers: {
                'Authorization': 'Bearer ' + localStorage.access_token
                , 'Accept': 'application/json'
                , 'Content-Type': 'application/json'
            }
            , error: function(error) {
                if (error.status == 401) {
                    jwtrefresh().then(data => {
                        this.headers = {
                            'Authorization': 'Bearer ' + localStorage.access_token
                        };
                        $.ajax(this);
                    }).catch(error => {
                        $('#errorModal').modal();
                    });
                }

                if (error.status == 500) {
                    $('#errorModal').modal();
                }
                if (error.status == 503) {
                    $('#serverping').empty();
                    $('#serverping').html('<i class="fas fa-times text-danger"></i>');
                }
            }
        });

        //JWT Token Refresh
        function jwtrefresh() {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/auth'
                    , type: 'GET'
                    , data: {
                        username: localStorage.username
                        , refresh_token: localStorage.refresh_token
                    }
                    , success: function(data) {
                        localStorage.access_token = data.access_token;
                        localStorage.refresh_token = data.refresh_token;
                        localStorage.username = data.username;
                        resolve(data);
                    }
                    , error: function(error) {
                        localStorage.clear();
                        window.location.replace('/login');
                        reject(error);
                    }
                });
            });
        }

        //Get Data for DataTable
        function getData(url, loading = true) {
            if (loading) {
                $('#mainloading').removeClass('d-none');
            }
            $.ajax({
                type: 'GET'
                , url: url
                , success: function(data) {
                    localStorage.dtdata = '';
                    localStorage.dtdata = JSON.stringify(data);
                    dtRender();
                    if (loading) {
                        setTimeout(function() {
                            $('#mainloading').addClass('d-none');
                        }, 250);
                    }
                }
            });
        }

        //Get Data for Other
        function getDataNoDT(url) {
            $.ajax({
                type: 'GET'
                , url: url
                , success: function(data) {
                    localStorage.otherdata = '';
                    localStorage.otherdata = JSON.stringify(data);
                }
            });
        }

        //Get Data for DataTable
        function getDataNoUI(url) {
            $.ajax({
                type: 'GET'
                , url: url
                , success: function(data) {
                    localStorage.dtdata = '';
                    localStorage.dtdata = JSON.stringify(data);
                }
            });
        }

        //Logout
        $('#logout').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '/auth'
                , type: 'DELETE'
                , headers: {
                    'x-csrf-token': $('meta[name="csrf-token"]').attr('content')
                    , 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                }
                , data: {
                    'username': localStorage.username
                    , 'refresh_token': localStorage.refresh_token
                }
                , success: function(data) {
                    localStorage.clear();
                    window.location.replace('/login');
                }
            });
        });

    </script>
    @yield('js')
    @stack('scripts')
</body>
</html>


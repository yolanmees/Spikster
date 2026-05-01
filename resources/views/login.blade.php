<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex">
    <meta name="cipi-version" content="{{ Storage::get('cipi/version.md') }}">
    <title>{{ config('cipi.name') }} | {{ __('spikster.login') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/favicon.png" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            var t = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
            if (t === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>
</head>

<body class="bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-soft border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-zinc-950 dark:bg-white mb-4">
                            <svg class="w-6 h-6 text-white dark:text-zinc-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-zinc-950 dark:text-white">{{ config('cipi.name') }}</h1>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('spikster.login') }}</p>
                    </div>
                    <form class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5"
                                for="username">
                                {{ __('spikster.username') }}
                            </label>
                            <input
                                class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                                id="username" type="email" placeholder="john.doe" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5"
                                for="password">
                                {{ __('spikster.password') }}
                            </label>
                            <input
                                class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all"
                                id="password" type="password" placeholder="••••••••" />
                        </div>
                        <div class="pt-1">
                            <button type="button"
                                class="w-full inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-zinc-950 hover:bg-zinc-800 text-white font-semibold rounded-lg active:scale-95 transition-all duration-150 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-zinc-200"
                                id="login">
                                <span id="loading" class="hidden">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                {{ __('spikster.login') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bg-zinc-50 dark:bg-zinc-950/50 px-8 py-4 border-t border-zinc-200 dark:border-zinc-800 text-center">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                        &copy;{{ Date('Y') }} - <a href="{{ config('cipi.website') }}" target="_blank"
                            class="text-purple-700 dark:text-purple-400 hover:underline"> {{ config('cipi.name') }}</a> -
                        v{{ Storage::get('cipi/version.md') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        //Clear current auth
        localStorage.clear();

        //Validation check
        function loginValidate() {
            validation = true;
            if (!$('#username').val()) {
                $('#username').removeClass('border-gray-200 dark:border-gray-600').addClass('border-red-500');
                validation = false;
            }
            if (!$('#password').val()) {
                $('#password').removeClass('border-gray-200 dark:border-gray-600').addClass('border-red-500');
                validation = false;
            }
            return validation;
        }

        //Login
        function loginSubmit() {
            if (loginValidate() == true) {
                $.ajax({
                    url: '/auth',
                    type: 'POST',
                    headers: {
                        'x-csrf-token': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        'username': $('#username').val(),
                        'password': $('#password').val()
                    },
                    beforeSend: function() {
                        $('#loading').removeClass('hidden');
                        $('#username').removeClass('border-red-500').addClass(
                            'border-zinc-200 dark:border-zinc-700');
                        $('#password').removeClass('border-red-500').addClass(
                            'border-zinc-200 dark:border-zinc-700');
                    },
                    complete: function() {
                        $('#username').blur();
                        $('#password').blur();
                        $('#loading').addClass('hidden');
                    },
                    success: function(data) {
                        localStorage.access_token = data.access_token;
                        localStorage.refresh_token = data.refresh_token;
                        localStorage.username = data.username;
                        window.location.replace('/dashboard');
                    },
                    error: function() {
                        $('#username').removeClass('border-zinc-200 dark:border-zinc-700').addClass(
                            'border-red-500');
                        $('#password').removeClass('border-zinc-200 dark:border-zinc-700').addClass(
                            'border-red-500');
                    }
                });
            }
        }

        //Keyup Validation
        $('#username').keyup(function() {
            $('#username').removeClass('border-red-500').addClass('border-zinc-200 dark:border-zinc-700');
        });
        $('#password').keyup(function() {
            $('#password').removeClass('border-red-500').addClass('border-zinc-200 dark:border-zinc-700');
        });
        $('#username').blur(function() {
            loginValidate();
        });
        $('#password').blur(function() {
            loginValidate();
        });

        //Submit via Mouse Click
        $('#login').on('click', function() {
            loginSubmit();
        });

        //Submit via Enter Key
        $(document).keypress(function(e) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if (keycode == '13') {
                loginSubmit();
            }
        });
    </script>

</body>

</html>

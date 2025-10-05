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
</head>

<body class="bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('spikster.login') }}</h1>
                    </div>
                    <form class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                for="username">
                                {{ __('spikster.username') }}
                            </label>
                            <input
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                id="username" type="email" placeholder="john.doe" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                for="password">
                                {{ __('spikster.password') }}
                            </label>
                            <input
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                id="password" type="password" placeholder="********" />
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="button"
                                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200"
                                id="login">
                                {{ __('spikster.login') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div
                    class="bg-gray-50 dark:bg-gray-900/50 px-8 py-4 border-t border-gray-200 dark:border-gray-700 text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        &copy{{ Date('Y') }} - <a href="{{ config('cipi.website') }}" target="_blank"
                            class="text-blue-600 dark:text-blue-400 hover:underline"> {{ config('cipi.name') }}</a> -
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
                            'border-gray-200 dark:border-gray-600');
                        $('#password').removeClass('border-red-500').addClass(
                            'border-gray-200 dark:border-gray-600');
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
                        $('#username').removeClass('border-gray-200 dark:border-gray-600').addClass(
                            'border-red-500');
                        $('#password').removeClass('border-gray-200 dark:border-gray-600').addClass(
                            'border-red-500');
                    }
                });
            }
        }

        //Keyup Validation
        $('#username').keyup(function() {
            $('#username').removeClass('border-red-500').addClass('border-gray-200 dark:border-gray-600');
        });
        $('#password').keyup(function() {
            $('#password').removeClass('border-red-500').addClass('border-gray-200 dark:border-gray-600');
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

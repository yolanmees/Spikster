<?php

    return [

        // Panel Credential
        'username'          => env('CIPI_USERNAME', 'admin@localhost'),
        'password'          => env('CIPI_PASSWORD', 'password'),

        // JWT Settings
        'jwt_secret'        => env('JWT_SECRET', env('APP_KEY')),
        'jwt_access'        => env('JWT_ACCESS', 900),
        'jwt_refresh'       => env('JWT_REFRESH', 7200),

        // Custom Vars
        'name'              => env('CIPI_NAME', 'Spikster Control Panel'),
        'website'           => env('CIPI_WEBSITE', 'https://github.com/yolanmees/Spikster'),
        'activesetupcount'  => env('CIPI_ACTIVESETUPCOUNT', '/#'),
        'documentation'     => env('CIPI_DOCUMENTATION', 'https://spikster.com/'),
        'app'               => env('CIPI_APP', '/#'),

        // Global Settings
        'users_prefix'      => env('CIPI_USERS_PREFIX', 'cp'),
        'phpvers'           => ['8.3', '8.2', '8.1','8.0','7.4'],
        'services'          => ['nginx','php','mysql','redis','supervisor'],
        'default_php'       => '8.3',

    ];

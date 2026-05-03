<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security-related settings for the application
    |
    */

    // API security
    'api' => [
        'rate_limit_per_minute' => env('API_RATE_LIMIT', 60),
    ],

    // Role-based access control
    'rbac' => [
        'panel_admin_bypass' => env('RBAC_PANEL_ADMIN_BYPASS', false),
        'panel_admin_identifier' => env('RBAC_PANEL_ADMIN_IDENTIFIER', 'admin@localhost'),
    ],

    // IP whitelist/blacklist
    'ip' => [
        'whitelist' => env('IP_WHITELIST', ''),
        'blacklist' => env('IP_BLACKLIST', ''),
        'enable_whitelist' => env('ENABLE_IP_WHITELIST', false),
    ],

    // Audit logging
    'audit' => [
        'enabled' => env('AUDIT_LOGGING', true),
        'log_channel' => env('AUDIT_LOG_CHANNEL', 'stack'),
        'events' => [
            'login',
            'logout',
            'server_create',
            'server_delete',
            'site_create',
            'site_delete',
            'password_change',
            'ssh_execution',
        ],
    ],

    // Brute force protection
    'brute_force' => [
        'max_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION', 900), // seconds (15 minutes)
        'enabled' => env('BRUTE_FORCE_PROTECTION', true),
    ],

    // Two-factor authentication
    '2fa' => [
        'enabled' => env('2FA_ENABLED', true),
        'required_for_admin' => env('2FA_REQUIRED_FOR_ADMIN', true),
    ],

    // Content Security Policy
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'report_uri' => env('CSP_REPORT_URI', null),
    ],

    // Security headers
    'headers' => [
        'x_frame_options' => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'referrer_policy' => 'strict-origin-when-cross-origin',
    ],

];

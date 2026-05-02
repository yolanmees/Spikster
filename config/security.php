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

    // Password requirements
    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', false),
    ],

    // Session security
    'session' => [
        'timeout' => env('SESSION_TIMEOUT', 120), // minutes
        'strict_mode' => env('SESSION_STRICT_MODE', true),
        'regenerate_on_login' => true,
    ],

    // API security
    'api' => [
        'rate_limit_per_minute' => env('API_RATE_LIMIT', 60),
    ],

    // Role-based access control
    'rbac' => [
        'panel_admin_bypass' => env('RBAC_PANEL_ADMIN_BYPASS', true),
        'panel_admin_identifier' => env('RBAC_PANEL_ADMIN_IDENTIFIER', 'admin@localhost'),
    ],

    // SSH security
    'ssh' => [
        'allowed_ports' => [22, 2222],
        'key_min_length' => 2048,
        'connection_timeout' => 30,
        'command_timeout' => 300,
    ],

    // File upload security
    'upload' => [
        'max_size' => env('MAX_UPLOAD_SIZE', 10240), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'tar', 'gz'],
        'scan_uploads' => env('SCAN_UPLOADS', false),
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
        'required_for_admin' => env('2FA_REQUIRED_FOR_ADMIN', false),
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

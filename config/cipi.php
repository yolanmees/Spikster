<?php

/**
 * Legacy branding configuration.
 *
 * All system settings (PHP versions, services, daemon tokens, etc.)
 * have been migrated to config/spikster.php and config/security.php.
 * This file now only holds branding strings for backward compatibility
 * with existing Blade templates.
 */

return [

    'name' => env('CIPI_NAME', 'Spikster Control Panel'),
    'website' => env('CIPI_WEBSITE', 'https://github.com/yolanmees/Spikster'),
    'documentation' => env('CIPI_DOCUMENTATION', 'https://spikster.com/'),
    'app' => env('CIPI_APP', '/#'),

];

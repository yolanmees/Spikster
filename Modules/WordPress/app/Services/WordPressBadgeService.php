<?php

namespace Modules\WordPress\Services;

use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Models\WordPressPlugin;
use Modules\WordPress\Models\WordPressTheme;

class WordPressBadgeService
{
    public function getPendingUpdatesCount(): int
    {
        $coreUpdates = WordPressInstallation::whereNotNull('version')
            ->whereHas('updates', function ($q) {
                $q->where('status', 'pending')->where('update_type', 'core');
            })
            ->count();

        $themeUpdates = WordPressTheme::whereNotNull('update_available')->count();
        $pluginUpdates = WordPressPlugin::whereNotNull('update_available')->count();

        return $coreUpdates + $themeUpdates + $pluginUpdates;
    }

    public function getInstallationsCount(): int
    {
        return WordPressInstallation::count();
    }

    public function getActiveInstallationsCount(): int
    {
        return WordPressInstallation::where('status', 'active')->count();
    }
}

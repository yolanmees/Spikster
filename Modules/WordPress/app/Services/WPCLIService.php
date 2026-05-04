<?php

namespace Modules\WordPress\Services;

use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Support\Facades\Log;
use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Models\WordPressPlugin;
use Modules\WordPress\Models\WordPressTheme;

class WPCLIService
{
    public function __construct(
        protected RemoteDaemonService $daemon
    ) {}

    /**
     * Execute a WP-CLI command on the remote server via the daemon.
     * The Go daemon validates the command against its own allowlist.
     */
    public function executeCommand(WordPressInstallation $installation, string $command): array
    {
        try {
            $server = $installation->site->server;
            $path   = $installation->getFullPath();

            $result = $this->daemon->send($server, 'wordpress.cli', [
                'username' => $installation->site->username,
                'path'     => $path,
                'command'  => $command,
            ]);

            return [
                'success' => $result['success'] ?? false,
                'output'  => $result['output'] ?? '',
            ];

        } catch (\Exception $e) {
            Log::error('WP-CLI command failed', [
                'command' => $command,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'output'  => $e->getMessage(),
            ];
        }
    }

    public function syncThemes(WordPressInstallation $installation): array
    {
        $this->updateWordPressVersion($installation);

        $result = $this->executeCommand($installation, 'theme list --format=json');
        if (! $result['success']) {
            return $result;
        }

        $themes = json_decode($result['output'], true);
        if (! is_array($themes)) {
            return ['success' => false, 'message' => 'Invalid response from WP-CLI'];
        }

        foreach ($themes as $themeData) {
            WordPressTheme::updateOrCreate(
                [
                    'wordpress_installation_id' => $installation->id,
                    'slug' => $themeData['name'],
                ],
                [
                    'name'             => $themeData['title'] ?? $themeData['name'],
                    'version'          => $themeData['version'] ?? 'Unknown',
                    'author'           => $themeData['author'] ?? null,
                    'description'      => $themeData['description'] ?? null,
                    'is_active'        => ($themeData['status'] === 'active'),
                    'status'           => $themeData['status'],
                    'update_available' => isset($themeData['update']) && $themeData['update'] !== 'none' ? $themeData['update'] : null,
                    'last_checked'     => now(),
                ]
            );
        }

        return ['success' => true, 'count' => count($themes)];
    }

    public function syncPlugins(WordPressInstallation $installation): array
    {
        $this->updateWordPressVersion($installation);

        $result = $this->executeCommand($installation, 'plugin list --format=json');
        if (! $result['success']) {
            return $result;
        }

        $plugins = json_decode($result['output'], true);
        if (! is_array($plugins)) {
            return ['success' => false, 'message' => 'Invalid response from WP-CLI'];
        }

        foreach ($plugins as $pluginData) {
            WordPressPlugin::updateOrCreate(
                [
                    'wordpress_installation_id' => $installation->id,
                    'slug' => $pluginData['name'],
                ],
                [
                    'name'             => $pluginData['title'] ?? $pluginData['name'],
                    'version'          => $pluginData['version'] ?? 'Unknown',
                    'author'           => $pluginData['author'] ?? null,
                    'description'      => $pluginData['description'] ?? null,
                    'is_active'        => ($pluginData['status'] === 'active'),
                    'status'           => $pluginData['status'],
                    'update_available' => isset($pluginData['update']) && $pluginData['update'] !== 'none' ? $pluginData['update'] : null,
                    'last_checked'     => now(),
                ]
            );
        }

        return ['success' => true, 'count' => count($plugins)];
    }

    public function checkCoreUpdate(WordPressInstallation $installation): array
    {
        $this->updateWordPressVersion($installation);
        $installation->refresh();

        $result = $this->executeCommand($installation, 'core check-update --format=json');

        if (! $result['success']) {
            return [
                'success'         => true,
                'has_update'      => false,
                'current_version' => $installation->version,
            ];
        }

        $updates = json_decode($result['output'], true);
        if (empty($updates) || ! is_array($updates)) {
            return [
                'success'         => true,
                'has_update'      => false,
                'current_version' => $installation->version,
            ];
        }

        $latestVersion = $updates[0]['version'] ?? null;

        return [
            'success'         => true,
            'has_update'      => ! empty($latestVersion),
            'current_version' => $installation->version,
            'new_version'     => $latestVersion,
        ];
    }

    public function activateTheme(WordPressInstallation $installation, string $themeSlug): array
    {
        return $this->executeCommand($installation, 'theme activate '.$themeSlug);
    }

    public function activatePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, 'plugin activate '.$pluginSlug);
    }

    public function deactivatePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, 'plugin deactivate '.$pluginSlug);
    }

    public function installTheme(WordPressInstallation $installation, string $themeSlug, bool $activate = false): array
    {
        $cmd = 'theme install '.$themeSlug.($activate ? ' --activate' : '');

        return $this->executeCommand($installation, $cmd);
    }

    public function installPlugin(WordPressInstallation $installation, string $pluginSlug, bool $activate = false): array
    {
        $cmd = 'plugin install '.$pluginSlug.($activate ? ' --activate' : '');

        return $this->executeCommand($installation, $cmd);
    }

    public function updateTheme(WordPressInstallation $installation, string $themeSlug): array
    {
        return $this->executeCommand($installation, 'theme update '.$themeSlug);
    }

    public function updatePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, 'plugin update '.$pluginSlug);
    }

    public function updateCore(WordPressInstallation $installation): array
    {
        return $this->executeCommand($installation, 'core update');
    }

    public function getInfo(WordPressInstallation $installation): array
    {
        $result = $this->executeCommand($installation, 'core version');
        if (! $result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'version' => trim($result['output']),
        ];
    }

    public function deleteTheme(WordPressInstallation $installation, string $themeSlug): array
    {
        return $this->executeCommand($installation, 'theme delete '.$themeSlug);
    }

    public function deletePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, 'plugin delete '.$pluginSlug.' --deactivate');
    }

    // ─── WordPress.org search — these use the Org API directly, no SSH/daemon ─

    protected function updateWordPressVersion(WordPressInstallation $installation): void
    {
        $result = $this->executeCommand($installation, 'core version');

        if ($result['success'] && preg_match('/(\d+\.\d+(?:\.\d+)?)/', $result['output'], $matches)) {
            $installation->update(['version' => $matches[1]]);
        } else {
            Log::warning('Could not extract WP version from WP-CLI output', [
                'installation_id' => $installation->id,
                'output'          => $result['output'] ?? '',
            ]);
        }
    }
}

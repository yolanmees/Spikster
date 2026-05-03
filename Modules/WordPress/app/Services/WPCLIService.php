<?php

namespace Modules\WordPress\Services;

use App\Services\SSHService;
use Illuminate\Support\Facades\Log;
use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Models\WordPressPlugin;
use Modules\WordPress\Models\WordPressTheme;

class WPCLIService
{
    public function __construct(
        protected SSHService $sshService
    ) {}

    public function executeCommand(WordPressInstallation $installation, string $command): array
    {
        // Validate command against allowed WP-CLI subcommands
        if (! $this->isSafeWpCliCommand($command)) {
            throw new \InvalidArgumentException('Unsafe WP-CLI command detected');
        }

        try {
            $server = $installation->site->server;
            $path = escapeshellarg($installation->getFullPath());

            $ssh = $this->sshService->connect($server);

            // Execute WP-CLI command as www-data user
            $fullCommand = "echo ".escapeshellarg($server->password)." | sudo -S -u www-data wp {$command} --path={$path} 2>&1";
            $output = $ssh->exec($fullCommand);

            $ssh->disconnect();

            return [
                'success' => ! str_contains(strtolower($output), 'error'),
                'output' => $output,
            ];

        } catch (\Exception $e) {
            Log::error('WP-CLI command failed', [
                'command' => $command,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'output' => $e->getMessage(),
            ];
        }
    }

    public function syncThemes(WordPressInstallation $installation): array
    {
        // First update WordPress version
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
                    'name' => $themeData['title'] ?? $themeData['name'],
                    'version' => $themeData['version'] ?? 'Unknown',
                    'author' => $themeData['author'] ?? null,
                    'description' => $themeData['description'] ?? null,
                    'is_active' => ($themeData['status'] === 'active'),
                    'status' => $themeData['status'],
                    'update_available' => isset($themeData['update']) && $themeData['update'] !== 'none' ? $themeData['update'] : null,
                    'last_checked' => now(),
                ]
            );
        }

        return [
            'success' => true,
            'count' => count($themes),
        ];
    }

    /**
     * Update WordPress version in database
     */
    protected function updateWordPressVersion(WordPressInstallation $installation): void
    {
        $result = $this->executeCommand($installation, 'core version');

        Log::info('WP-CLI core version command', [
            'installation_id' => $installation->id,
            'success' => $result['success'],
            'output' => $result['output'],
        ]);

        if ($result['success']) {
            $output = $result['output'];

            // Extract version number from output (e.g., "6.3.1" or "WordPress 6.3.1")
            if (preg_match('/(\d+\.\d+(?:\.\d+)?)/', $output, $matches)) {
                $version = $matches[1];

                Log::info('Updating WordPress version', [
                    'installation_id' => $installation->id,
                    'old_version' => $installation->version,
                    'new_version' => $version,
                ]);

                $installation->update(['version' => $version]);
            } else {
                Log::warning('Could not extract version from WP-CLI output', [
                    'installation_id' => $installation->id,
                    'output' => $output,
                ]);
            }
        } else {
            Log::error('WP-CLI core version command failed', [
                'installation_id' => $installation->id,
                'output' => $result['output'],
            ]);
        }
    }

    public function syncPlugins(WordPressInstallation $installation): array
    {
        // First update WordPress version
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
                    'name' => $pluginData['title'] ?? $pluginData['name'],
                    'version' => $pluginData['version'] ?? 'Unknown',
                    'author' => $pluginData['author'] ?? null,
                    'description' => $pluginData['description'] ?? null,
                    'is_active' => ($pluginData['status'] === 'active'),
                    'status' => $pluginData['status'],
                    'update_available' => isset($pluginData['update']) && $pluginData['update'] !== 'none' ? $pluginData['update'] : null,
                    'last_checked' => now(),
                ]
            );
        }

        return [
            'success' => true,
            'count' => count($plugins),
        ];
    }

    public function checkCoreUpdate(WordPressInstallation $installation): array
    {
        // First update current version
        $this->updateWordPressVersion($installation);
        $installation->refresh();

        $result = $this->executeCommand($installation, 'core check-update --format=json');

        if (! $result['success']) {
            return [
                'success' => true,
                'has_update' => false,
                'current_version' => $installation->version,
            ];
        }

        $updates = json_decode($result['output'], true);

        if (empty($updates) || ! is_array($updates)) {
            return [
                'success' => true,
                'has_update' => false,
                'current_version' => $installation->version,
            ];
        }

        $latestVersion = $updates[0]['version'] ?? null;

        return [
            'success' => true,
            'has_update' => ! empty($latestVersion),
            'current_version' => $installation->version,
            'new_version' => $latestVersion,
        ];
    }

    public function activateTheme(WordPressInstallation $installation, string $themeSlug): array
    {
        return $this->executeCommand($installation, "theme activate {$themeSlug}");
    }

    public function activatePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, "plugin activate {$pluginSlug}");
    }

    public function deactivatePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, "plugin deactivate {$pluginSlug}");
    }

    public function installTheme(WordPressInstallation $installation, string $themeSlug, bool $activate = false): array
    {
        $activateFlag = $activate ? '--activate' : '';

        return $this->executeCommand($installation, "theme install {$themeSlug} {$activateFlag}");
    }

    public function installPlugin(WordPressInstallation $installation, string $pluginSlug, bool $activate = false): array
    {
        $activateFlag = $activate ? '--activate' : '';

        return $this->executeCommand($installation, "plugin install {$pluginSlug} {$activateFlag}");
    }

    public function updateTheme(WordPressInstallation $installation, string $themeSlug): array
    {
        return $this->executeCommand($installation, "theme update {$themeSlug}");
    }

    public function updatePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, "plugin update {$pluginSlug}");
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

    /**
     * Search for themes in WordPress.org repository
     */
    public function searchThemes(WordPressInstallation $installation, string $searchTerm = '', array $filters = []): array
    {
        $command = "theme search {$searchTerm} --format=json";

        // Add filters
        if (! empty($filters['per_page'])) {
            $command .= " --per-page={$filters['per_page']}";
        }
        if (! empty($filters['page'])) {
            $command .= " --page={$filters['page']}";
        }
        if (! empty($filters['fields'])) {
            $fields = implode(',', $filters['fields']);
            $command .= " --fields={$fields}";
        }

        $result = $this->executeCommand($installation, $command);

        if (! $result['success']) {
            return $result;
        }

        $themes = json_decode($result['output'], true);

        return [
            'success' => true,
            'themes' => $themes ?? [],
            'count' => count($themes ?? []),
        ];
    }

    /**
     * Search for plugins in WordPress.org repository
     */
    public function searchPlugins(WordPressInstallation $installation, string $searchTerm = '', array $filters = []): array
    {
        $command = "plugin search {$searchTerm} --format=json";

        // Add filters
        if (! empty($filters['per_page'])) {
            $command .= " --per-page={$filters['per_page']}";
        }
        if (! empty($filters['page'])) {
            $command .= " --page={$filters['page']}";
        }
        if (! empty($filters['fields'])) {
            $fields = implode(',', $filters['fields']);
            $command .= " --fields={$fields}";
        }

        $result = $this->executeCommand($installation, $command);

        if (! $result['success']) {
            return $result;
        }

        $plugins = json_decode($result['output'], true);

        return [
            'success' => true,
            'plugins' => $plugins ?? [],
            'count' => count($plugins ?? []),
        ];
    }

    /**
     * Get theme info from WordPress.org
     */
    public function getThemeInfo(WordPressInstallation $installation, string $themeSlug): array
    {
        $result = $this->executeCommand($installation, "theme search {$themeSlug} --format=json");

        if (! $result['success']) {
            return $result;
        }

        $themes = json_decode($result['output'], true);
        $theme = collect($themes)->firstWhere('slug', $themeSlug);

        if (! $theme) {
            return [
                'success' => false,
                'message' => 'Theme not found in WordPress.org repository',
            ];
        }

        return [
            'success' => true,
            'theme' => $theme,
        ];
    }

    /**
     * Get plugin info from WordPress.org
     */
    public function getPluginInfo(WordPressInstallation $installation, string $pluginSlug): array
    {
        $result = $this->executeCommand($installation, "plugin search {$pluginSlug} --format=json");

        if (! $result['success']) {
            return $result;
        }

        $plugins = json_decode($result['output'], true);
        $plugin = collect($plugins)->firstWhere('slug', $pluginSlug);

        if (! $plugin) {
            return [
                'success' => false,
                'message' => 'Plugin not found in WordPress.org repository',
            ];
        }

        return [
            'success' => true,
            'plugin' => $plugin,
        ];
    }

    /**
     * Delete theme
     */
    public function deleteTheme(WordPressInstallation $installation, string $themeSlug): array
    {
        return $this->executeCommand($installation, "theme delete {$themeSlug}");
    }

    /**
     * Delete plugin
     */
    public function deletePlugin(WordPressInstallation $installation, string $pluginSlug): array
    {
        return $this->executeCommand($installation, "plugin delete {$pluginSlug}");
    }

    /**
     * Validate that a WP-CLI command only contains allowed subcommands and safe characters.
     */
    protected function isSafeWpCliCommand(string $command): bool
    {
        $allowedSubcommands = [
            'core', 'plugin', 'theme', 'user', 'option', 'post', 'term',
            'menu', 'widget', 'sidebar', 'db', 'config', 'cap', 'role',
            'transient', 'cron', 'cache', 'site', 'network', 'i18n',
            'language', 'maintenance-mode', 'scaffold', 'search-replace',
            'media', 'comment', 'taxonomy', 'export', 'import', 'rewrite',
            'super-admin', 'package', 'server', 'eval-file',
        ];

        $command = trim($command);
        $firstWord = strtolower(explode(' ', $command, 2)[0] ?? '');

        if (! in_array($firstWord, $allowedSubcommands, true)) {
            return false;
        }

        // Block shell metacharacters and dangerous patterns
        if (preg_match('/[;&|`$(){}[\]!<>#~*?"\'\n\r\t\\\\]/', $command)) {
            return false;
        }

        return true;
    }
}

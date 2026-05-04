<?php

namespace Modules\WordPress\Services;

use App\Models\Site;
use App\Services\DatabaseService;
use App\Services\RemoteDaemonService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\WordPress\Models\WordPressInstallation;

class WordPressInstallationService
{
    public function __construct(
        protected DatabaseService $databaseService,
        protected RemoteDaemonService $daemon
    ) {}

    public function install(array $data): array
    {
        try {
            $site = Site::where('site_id', $data['site_id'])->first();
            if (! $site) {
                return ['success' => false, 'message' => 'Site not found'];
            }

            $server = $site->server;
            if (! $server) {
                return ['success' => false, 'message' => 'Server not found'];
            }

            // Create database and user
            $dbName     = 'wp_'.Str::random(8);
            $dbUser     = 'user_'.Str::random(8);
            $dbPassword = Str::random(16);

            $databaseResponse = $this->databaseService->createDatabase($dbName, $data['site_id']);
            if (! $databaseResponse['success']) {
                return $databaseResponse;
            }

            $userResponse = $this->databaseService->createUser($dbUser, $dbPassword, $data['site_id']);
            if (! $userResponse['success']) {
                return $userResponse;
            }

            $linkResponse = $this->databaseService->linkDatabaseUser(
                $userResponse['user']->id,
                $databaseResponse['database']->id,
                $data['site_id']
            );
            if (! $linkResponse['success']) {
                return $linkResponse;
            }

            // Build full path
            $path = $site->rootpath.'/'.trim($data['path'], '/');

            // Step 1 — install WP files + wp-config.php via daemon
            $filesResult = $this->daemon->send($server, 'wordpress.install-files', [
                'username'    => $site->username,
                'path'        => $path,
                'db_name'     => $dbName,
                'db_user'     => $dbUser,
                'db_password' => $dbPassword,
            ]);

            if (! ($filesResult['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => 'WordPress file installation failed: '.($filesResult['error'] ?? 'unknown error'),
                ];
            }

            // Step 2 — run wp core install via daemon
            $url   = $data['url'] ?? 'https://'.$site->domain.'/'.trim($data['path'], '/');
            $email = $data['admin_email'] ?? 'admin@'.$site->domain;

            $coreResult = $this->daemon->send($server, 'wordpress.core-install', [
                'username'    => $site->username,
                'path'        => $path,
                'url'         => $url,
                'title'       => $data['title'] ?? $site->domain,
                'admin_user'  => $data['username'],
                'admin_pass'  => $data['password'],
                'admin_email' => $email,
                'locale'      => $data['locale'] ?? 'en_US',
            ]);

            if (! ($coreResult['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => 'WordPress core install failed: '.($coreResult['error'] ?? 'unknown error'),
                ];
            }

            // Save installation record
            $installation = WordPressInstallation::create([
                'site_id'          => $data['site_id'],
                'path'             => $data['path'],
                'url'              => $url,
                'admin_username'   => $data['username'],
                'admin_password'   => $data['password'],
                'database_id'      => $databaseResponse['database']->id,
                'database_user_id' => $userResponse['user']->id,
                'locale'           => $data['locale'] ?? 'en_US',
                'auto_update'      => $data['auto_update'] ?? false,
                'status'           => 'active',
                'installed_at'     => now(),
            ]);

            return [
                'success'      => true,
                'message'      => "WordPress installed successfully at {$path}",
                'installation' => $installation,
                'db_credentials' => [
                    'name'     => $dbName,
                    'user'     => $dbUser,
                    'password' => $dbPassword,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('WordPress installation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'WordPress installation failed: '.$e->getMessage(),
            ];
        }
    }

    public function uninstall(WordPressInstallation $installation): array
    {
        try {
            $site   = $installation->site;
            $server = $site->server;
            $path   = $installation->getFullPath();

            // Remove files via daemon
            $result = $this->daemon->send($server, 'wordpress.uninstall-files', ['path' => $path]);
            if (! ($result['success'] ?? false)) {
                Log::warning('WordPress file removal failed', [
                    'path'  => $path,
                    'error' => $result['error'] ?? '',
                ]);
            }

            // Delete database
            $this->databaseService->deleteDatabase($installation->database_id, $installation->site_id);

            // Delete installation record
            $installation->delete();

            return [
                'success' => true,
                'message' => 'WordPress uninstalled successfully',
            ];

        } catch (\Exception $e) {
            Log::error('WordPress uninstallation failed', [
                'error'           => $e->getMessage(),
                'installation_id' => $installation->id,
            ]);

            return [
                'success' => false,
                'message' => 'Uninstallation failed: '.$e->getMessage(),
            ];
        }
    }
}

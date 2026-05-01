<?php

namespace Modules\WordPress\Services;

use App\Models\Site;
use App\Services\DatabaseService;
use App\Services\SSHService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\WordPress\Models\WordPressInstallation;

class WordPressInstallationService
{
    public function __construct(
        protected DatabaseService $databaseService,
        protected SSHService $sshService
    ) {}

    public function install(array $data): array
    {
        try {
            // Get site and server
            $site = Site::where('site_id', $data['site_id'])->first();
            if (! $site) {
                return ['success' => false, 'message' => 'Site not found'];
            }

            $server = $site->server;
            if (! $server) {
                return ['success' => false, 'message' => 'Server not found'];
            }

            // Create database and user
            $dbName = 'wp_'.Str::random(8);
            $dbUser = 'user_'.Str::random(8);
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

            // Install WordPress files
            $path = $site->rootpath.'/'.trim($data['path'], '/');
            $installResult = $this->installWordPressFiles($server, $path, $dbName, $dbUser, $dbPassword);

            if (! $installResult['success']) {
                return $installResult;
            }

            // Create installation record
            $installation = WordPressInstallation::create([
                'site_id' => $data['site_id'],
                'path' => $data['path'],
                'url' => $data['url'] ?? 'https://'.$site->domain.'/'.trim($data['path'], '/'),
                'admin_username' => $data['username'],
                'admin_password' => $data['password'],
                'database_id' => $databaseResponse['database']->id,
                'database_user_id' => $userResponse['user']->id,
                'locale' => $data['locale'] ?? 'en_US',
                'status' => 'active',
                'installed_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "WordPress installed successfully at {$path}",
                'installation' => $installation,
                'db_credentials' => [
                    'name' => $dbName,
                    'user' => $dbUser,
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

    protected function installWordPressFiles($server, string $path, string $dbName, string $dbUser, string $dbPassword): array
    {
        try {
            $ssh = $this->sshService->connect($server);

            // Create directory
            Log::info('Creating WordPress directory', ['path' => $path]);
            $ssh->exec("echo '{$server->password}' | sudo -S mkdir -p {$path}");

            // Verify directory
            $dirCheck = trim($ssh->exec("test -d {$path} && echo 'exists' || echo 'not found'"));
            if ($dirCheck !== 'exists') {
                return ['success' => false, 'message' => 'Failed to create installation directory'];
            }

            // Download WordPress
            Log::info('Downloading WordPress');
            $downloadCmd = "echo '{$server->password}' | sudo -S curl -L -o {$path}/wordpress.tar.gz https://wordpress.org/latest.tar.gz 2>&1";
            $ssh->exec($downloadCmd);

            // Verify download
            $checkDownload = trim($ssh->exec("test -f {$path}/wordpress.tar.gz && echo 'exists' || echo 'not found'"));
            if ($checkDownload !== 'exists') {
                return ['success' => false, 'message' => 'Failed to download WordPress'];
            }

            // Check file size
            $fileSize = trim($ssh->exec("stat -c%s {$path}/wordpress.tar.gz 2>&1 || stat -f%z {$path}/wordpress.tar.gz 2>&1"));
            if (intval($fileSize) < 1000000) {
                return ['success' => false, 'message' => 'Downloaded file is too small or corrupt'];
            }

            // Extract WordPress
            Log::info('Extracting WordPress');
            $ssh->exec("echo '{$server->password}' | sudo -S tar -xzf {$path}/wordpress.tar.gz -C {$path} 2>&1");

            // Move files from wordpress/ subdirectory
            $ssh->exec("echo '{$server->password}' | sudo -S bash -c 'shopt -s dotglob && mv {$path}/wordpress/* {$path}/ && rmdir {$path}/wordpress && rm {$path}/wordpress.tar.gz' 2>&1");

            // Verify extraction
            $checkFile = trim($ssh->exec("test -f {$path}/wp-config-sample.php && echo 'exists' || echo 'not found'"));
            if ($checkFile !== 'exists') {
                return ['success' => false, 'message' => 'Failed to extract WordPress files'];
            }

            // Create wp-config.php
            $ssh->exec("echo '{$server->password}' | sudo -S cp {$path}/wp-config-sample.php {$path}/wp-config.php");

            // Update database credentials
            $ssh->exec("echo '{$server->password}' | sudo -S sed -i 's/database_name_here/{$dbName}/g' {$path}/wp-config.php");
            $ssh->exec("echo '{$server->password}' | sudo -S sed -i 's/username_here/{$dbUser}/g' {$path}/wp-config.php");
            $ssh->exec("echo '{$server->password}' | sudo -S sed -i 's/password_here/{$dbPassword}/g' {$path}/wp-config.php");

            // Generate security keys
            $authKey = Str::random(64);
            $ssh->exec("echo '{$server->password}' | sudo -S sed -i \"s/put your unique phrase here/{$authKey}/\" {$path}/wp-config.php");

            // Set permissions
            $ssh->exec("echo '{$server->password}' | sudo -S chown -R www-data:www-data {$path}");
            $ssh->exec("echo '{$server->password}' | sudo -S find {$path} -type d -exec chmod 755 {} \\;");
            $ssh->exec("echo '{$server->password}' | sudo -S find {$path} -type f -exec chmod 644 {} \\;");

            $ssh->disconnect();

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('WordPress files installation failed', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to install WordPress files: '.$e->getMessage(),
            ];
        }
    }

    public function uninstall(WordPressInstallation $installation): array
    {
        try {
            $site = $installation->site;
            $server = $site->server;
            $path = $installation->getFullPath();

            // Remove files via SSH
            $ssh = $this->sshService->connect($server);
            $ssh->exec("echo '{$server->password}' | sudo -S rm -rf {$path}");
            $ssh->disconnect();

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
                'error' => $e->getMessage(),
                'installation_id' => $installation->id,
            ]);

            return [
                'success' => false,
                'message' => 'Uninstallation failed: '.$e->getMessage(),
            ];
        }
    }
}

<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Wordpress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WordPressService
{
    protected $databaseService;
    protected $daemon;

    public function __construct(DatabaseService $databaseService, DaemonService $daemon)
    {
        $this->databaseService = $databaseService;
        $this->daemon = $daemon;
    }

    public function deployWordPress($path, $username, $password, $site_id)
    {
        try {
            $site = Site::where('site_id', $site_id)->first();
            if (! $site) {
                return ['success' => false, 'message' => 'Site not found'];
            }

            // Create database and user
            $dbName = 'wp_' . Str::random(8);
            $dbUser = 'user_' . Str::random(8);
            $dbPassword = Str::random(16);

            $databaseResponse = $this->databaseService->createDatabase($dbName, $site_id);
            if (! $databaseResponse['success']) {
                return $databaseResponse;
            }

            $userResponse = $this->databaseService->createUser($dbUser, $dbPassword, $site_id);
            if (! $userResponse['success']) {
                return $userResponse;
            }

            $linkResponse = $this->databaseService->linkDatabaseUser(
                $userResponse['user']->id,
                $databaseResponse['database']->id,
                $site_id
            );
            if (! $linkResponse['success']) {
                return $linkResponse;
            }

            // Build install script
            $authKey = Str::random(64);
            $escapedPath = escapeshellarg($path);
            $escapedDb = escapeshellarg($dbName);
            $escapedUser = escapeshellarg($dbUser);
            $escapedPass = escapeshellarg($dbPassword);
            $escapedKey = escapeshellarg($authKey);

            $script = <<<BASH
set -e
mkdir -p {$path}
curl -sL -o /tmp/wordpress.tar.gz https://wordpress.org/latest.tar.gz
tar -xzf /tmp/wordpress.tar.gz -C /tmp
shopt -s dotglob && mv /tmp/wordpress/* {$path}/ && rmdir /tmp/wordpress
rm -f /tmp/wordpress.tar.gz
cp {$path}/wp-config-sample.php {$path}/wp-config.php
sed -i "s/database_name_here/{$dbName}/" {$path}/wp-config.php
sed -i "s/username_here/{$dbUser}/" {$path}/wp-config.php
sed -i "s/password_here/{$dbPassword}/" {$path}/wp-config.php
sed -i "s/put your unique phrase here/{$authKey}/g" {$path}/wp-config.php
chown -R www-data:www-data {$path}
find {$path} -type d -exec chmod 755 {} \;
find {$path} -type f -exec chmod 644 {} \;
echo "WP_DONE"
BASH;

            Log::info('WordPress: running install script via daemon', ['path' => $path, 'site_id' => $site_id]);

            $result = $this->daemon->send('site.deploy-script', [
                'username' => $site->username,
                'script'   => $script,
            ]);

            $output = $result['output'] ?? '';
            Log::info('WordPress: deploy script result', ['output' => $output, 'success' => $result['success'] ?? false]);

            if (! ($result['success'] ?? false) || ! str_contains($output, 'WP_DONE')) {
                return [
                    'success' => false,
                    'message' => 'WordPress installation failed: ' . ($result['error'] ?? $output ?: 'unknown error'),
                ];
            }

            // Save record
            $wordpress = new Wordpress;
            $wordpress->path = $path;
            $wordpress->username = $username;
            $wordpress->password = $password;
            $wordpress->site_id = $site_id;
            $wordpress->database_id = $databaseResponse['database']->id;
            $wordpress->database_user_id = $userResponse['user']->id;
            $wordpress->save();

            return [
                'success'    => true,
                'message'    => "WordPress deployed successfully! Database: {$dbName}",
                'wordpress'  => $wordpress,
                'db_name'    => $dbName,
                'db_user'    => $dbUser,
                'db_password' => $dbPassword,
            ];

        } catch (\Exception $e) {
            Log::error('WordPress deployment exception: ' . $e->getMessage(), [
                'path'    => $path,
                'site_id' => $site_id,
            ]);

            return [
                'success' => false,
                'message' => 'WordPress deployment failed: ' . $e->getMessage(),
            ];
        }
    }
}

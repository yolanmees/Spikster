<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Wordpress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WordPressService
{
    protected $databaseService;
    protected $sshService;

    public function __construct(DatabaseService $databaseService, SSHService $sshService)
    {
        $this->databaseService = $databaseService;
        $this->sshService = $sshService;
    }

    public function deployWordPress($path, $username, $password, $site_id)
    {
        try {
            // Get the site to access server details
            $site = Site::where('site_id', $site_id)->first();
            if (!$site) {
                return ['success' => false, 'message' => 'Site not found'];
            }

            $server = $site->server;
            if (!$server) {
                return ['success' => false, 'message' => 'Server not found'];
            }

            // Create database and user
            $dbName = 'wp_' . Str::random(8);
            $dbUser = 'user_' . Str::random(8);
            $dbPassword = Str::random(16);

            $databaseResponse = $this->databaseService->createDatabase($dbName, $site_id);
            if (!$databaseResponse['success']) {
                return $databaseResponse;
            }

            $userResponse = $this->databaseService->createUser($dbUser, $dbPassword, $site_id);
            if (!$userResponse['success']) {
                return $userResponse;
            }

            $linkResponse = $this->databaseService->linkDatabaseUser(
                $userResponse['user']->id,
                $databaseResponse['database']->id,
                $site_id
            );
            if (!$linkResponse['success']) {
                return $linkResponse;
            }

            // Download and extract WordPress via SSH
            $ssh = $this->sshService->connect($server);

            // Create installation directory with sudo
            Log::info('Creating WordPress directory', ['path' => $path]);
            $ssh->exec("sudo mkdir -p {$path}");

            // Verify directory was created
            $dirCheck = trim($ssh->exec("test -d {$path} && echo 'exists' || echo 'not found'"));
            if ($dirCheck !== 'exists') {
                Log::error('Failed to create directory', ['path' => $path]);
                return ['success' => false, 'message' => 'Failed to create installation directory'];
            }

            // Download WordPress using curl (more reliable than wget with sudo)
            Log::info('Downloading WordPress', ['path' => $path]);
            $downloadCmd = "sudo curl -L -o {$path}/wordpress.tar.gz https://wordpress.org/latest.tar.gz 2>&1";
            $downloadResult = $ssh->exec($downloadCmd);
            Log::info('Download result', ['output' => $downloadResult]);

            // Check if download was successful and verify file size
            $checkDownload = trim($ssh->exec("test -f {$path}/wordpress.tar.gz && echo 'exists' || echo 'not found'"));
            if ($checkDownload !== 'exists') {
                Log::error('WordPress download failed - file not found', ['result' => $downloadResult, 'path' => $path]);
                return ['success' => false, 'message' => 'Failed to download WordPress. File not created.'];
            }

            // Check file size (WordPress tar.gz should be > 10MB)
            $fileSize = trim($ssh->exec("stat -c%s {$path}/wordpress.tar.gz 2>&1 || stat -f%z {$path}/wordpress.tar.gz 2>&1"));
            Log::info('Downloaded file size', ['size' => $fileSize, 'path' => $path]);

            if (intval($fileSize) < 1000000) {
                Log::error('WordPress download failed - file too small', ['size' => $fileSize]);
                // Show file content for debugging
                $fileContent = $ssh->exec("sudo head -20 {$path}/wordpress.tar.gz");
                Log::error('File content preview', ['content' => $fileContent]);
                return ['success' => false, 'message' => 'Failed to download WordPress. Downloaded file is too small or corrupt.'];
            }

            // Extract WordPress (tar extracts to wordpress/ subdirectory by default)
            Log::info('Extracting WordPress', ['path' => $path]);
            $extractCmd = "sudo tar -xzf {$path}/wordpress.tar.gz -C {$path} 2>&1";
            $extractResult = $ssh->exec($extractCmd);
            Log::info('Extract result', ['output' => $extractResult]);

            // Check if extraction created the wordpress directory
            $checkWordPressDir = trim($ssh->exec("test -d {$path}/wordpress && echo 'exists' || echo 'not found'"));
            if ($checkWordPressDir !== 'exists') {
                Log::error('WordPress extraction failed - wordpress directory not created', [
                    'extract_result' => $extractResult,
                    'path' => $path
                ]);
                return ['success' => false, 'message' => 'Failed to extract WordPress archive.'];
            }

            // Move files from wordpress/ subdirectory to main path
            $moveCmd = "sudo bash -c 'shopt -s dotglob && mv {$path}/wordpress/* {$path}/ && rmdir {$path}/wordpress && rm {$path}/wordpress.tar.gz' 2>&1";
            $moveResult = $ssh->exec($moveCmd);
            Log::info('Move result', ['output' => $moveResult]);

            // List files to verify
            $listFiles = $ssh->exec("ls -la {$path} | head -20");
            Log::info('Files after extraction', ['files' => $listFiles]);

            // Check if wp-config-sample.php exists
            $checkFile = trim($ssh->exec("test -f {$path}/wp-config-sample.php && echo 'exists' || echo 'not found'"));
            if ($checkFile !== 'exists') {
                Log::error('wp-config-sample.php not found after extraction', [
                    'path' => $path,
                    'extract_result' => $extractResult,
                    'move_result' => $moveResult,
                    'files' => $listFiles
                ]);
                return ['success' => false, 'message' => 'Failed to extract WordPress files. wp-config-sample.php not found.'];
            }

            // Create wp-config.php from sample
            $ssh->exec("sudo cp {$path}/wp-config-sample.php {$path}/wp-config.php");

            // Update database credentials in wp-config.php
            $ssh->exec("sudo sed -i 's/database_name_here/{$dbName}/g' {$path}/wp-config.php");
            $ssh->exec("sudo sed -i 's/username_here/{$dbUser}/g' {$path}/wp-config.php");
            $ssh->exec("sudo sed -i 's/password_here/{$dbPassword}/g' {$path}/wp-config.php");

            // Generate and set security keys
            $authKey = Str::random(64);

            $ssh->exec("sudo sed -i \"s/put your unique phrase here/{$authKey}/\" {$path}/wp-config.php");

            // Set proper permissions (files owned by www-data for web server access)
            $ssh->exec("sudo chown -R www-data:www-data {$path}");
            $ssh->exec("sudo find {$path} -type d -exec chmod 755 {} \\;");
            $ssh->exec("sudo find {$path} -type f -exec chmod 644 {} \\;");

            $ssh->disconnect();

            // Save WordPress installation record
            $wordpress = new Wordpress;
            $wordpress->path = $path;
            $wordpress->username = $username;
            $wordpress->password = $password;
            $wordpress->site_id = $site_id;
            $wordpress->database_id = $databaseResponse['database']->id;
            $wordpress->database_user_id = $userResponse['user']->id;
            $wordpress->save();

            return [
                'success' => true,
                'message' => "WordPress deployed successfully! Database: {$dbName}",
                'wordpress' => $wordpress,
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_password' => $dbPassword
            ];

        } catch (\Exception $e) {
            Log::error('WordPress deployment exception: ' . $e->getMessage(), [
                'path' => $path,
                'site_id' => $site_id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'WordPress deployment failed: ' . $e->getMessage()
            ];
        }
    }
}

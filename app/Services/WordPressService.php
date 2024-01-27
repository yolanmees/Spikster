<?php

namespace App\Services;

use App\Services\DatabaseService;
use Illuminate\Support\Str; 
use App\Models\Wordpress;

class WordPressService
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function deployWordPress($path, $username, $password, $site_id)
    {
        $dbName = 'wp_' . Str::random(8); 
        $dbUser = 'user_' . Str::random(8);
        $dbPassword = Str::random(16); 

        $databaseResponse = $this->databaseService->createDatabase($dbName, $site_id);
        if(!$databaseResponse['success']) {
            return $databaseResponse; 
        }
        $userResponse = $this->databaseService->createUser($dbUser, $dbPassword, $site_id);
        if(!$userResponse['success']) {
            return $userResponse; 
        }

        $linkResponse = $this->databaseService->linkDatabaseUser($userResponse['user']->id, $databaseResponse['database']->id, $site_id);
        if(!$linkResponse['success']) {
            return $linkResponse; 
        }

        $wpZip = file_get_contents('https://wordpress.org/latest.zip');
        $tempZip = tempnam(sys_get_temp_dir(), 'wordpress');
        file_put_contents($tempZip, $wpZip);
        $zip = new \ZipArchive();
        if($zip->open($tempZip) === true) {
            $zip->extractTo($path);
            $zip->close();
        } else {
            return ['success' => false, 'message' => 'Cannot open WordPress archive'];
        }

        $wpConfigSamplePath = $path . '/wordpress/wp-config-sample.php';
        $wpConfigPath = $path . '/wordpress/wp-config.php';

        $wpConfigSampleFile = fopen($wpConfigSamplePath, 'r');
        $wpConfigFile = fopen($wpConfigPath, 'w');

        if ($wpConfigSampleFile && $wpConfigFile) {
            while (($line = fgets($wpConfigSampleFile)) !== false) {
                $line = str_replace(['database_name_here', 'username_here', 'password_here'], [$dbName, $dbUser, $dbPassword], $line);
                $line = str_replace('put your unique phrase here', Str::random(64), $line);
                fwrite($wpConfigFile, $line);
            }
            fclose($wpConfigSampleFile);
            fclose($wpConfigFile);
        } else {
            return ['success' => false, 'message' => 'Failed to open files'];
        }
        

        return ['success' => true, 'message' => 'WordPress deployed successfully with database ' . $dbName];
    }
}
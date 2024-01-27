<?php

namespace App\Services;

use App\Services\DatabaseService;
use Illuminate\Support\Str; 

class WordPressService
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function deployWordPress($path, $username, $password)
    {
        $dbName = 'wp_' . Str::random(8); 
        $dbUser = 'user_' . Str::random(8);
        $dbPassword = Str::random(16); 

        $databaseResponse = $this->databaseService->createDatabase($dbName);
        if(!$databaseResponse['success']) {
            return $databaseResponse; 
        }
        
        $userResponse = $this->databaseService->createUser($dbUser, $dbPassword);
        if(!$userResponse['success']) {
            return $userResponse; 
        }

        $linkResponse = $this->databaseService->linkDatabaseUser($dbUser, $dbName);
        if(!$linkResponse['success']) {
            return $linkResponse; 
        }

        // Stap 2: Download en pak WordPress uit
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

        $wpConfigSample = file_get_contents($path . '/wordpress/wp-config-sample.php');
        $wpConfig = str_replace(['database_name_here', 'username_here', 'password_here'], [$dbName, $dbUser, $dbPassword], $wpConfigSample);
        $wpConfig = str_replace('put your unique phrase here', Str::random(64), $wpConfig); 
        file_put_contents($path . '/wordpress/wp-config.php', $wpConfig);

        

        return ['success' => true, 'message' => 'WordPress deployed successfully with database ' . $dbName];
    }
}
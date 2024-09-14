<?php

namespace App\Services;

use PDO;
use PDOException;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\DatabaseUserLink;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class DatabaseService
{

    protected function pdoConnect() {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $root = env('DB_USERNAME', 'spikster');
        $password = env('DB_PASSWORD', 'mgfjodm0nmi2ytuxnmu4yta1m2flyzkx');

        try {
            return new PDO("mysql:host={$host};port={$port}", $root, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }
    }

    public function createDatabase($databaseName, $siteId)
    {
        $pdo = $this->pdoConnect();

        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$databaseName`;");

            $database = new Database();
            $database->user_id = 1;
            $database->database_name = $databaseName;
            $database->site_id = $siteId;
            $database->save();

            return ['success' => true, 'message' => 'Database created successfully.', 'database' => $database];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Unable to create database. Error: " . $e->getMessage()];
        }
    }

    public function createUser($username, $password, $siteId)
    {
        $pdo = $this->pdoConnect();

        try {
            $stmt = $pdo->query("SELECT user FROM mysql.user WHERE user = '$username';");
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => "User '$username' already exists."];
            }

            $pdo->exec("CREATE USER '$username'@'%' IDENTIFIED BY '$password';");
            $pdo->exec("FLUSH PRIVILEGES;");

            $databaseUser = new DatabaseUser();
            $databaseUser->user_id = Auth::id();
            $databaseUser->username = $username;
            $databaseUser->password = Hash::make($password);
            $databaseUser->site_id = $siteId;
            $databaseUser->save();

            return ['success' => true, 'message' => 'User added successfully.', 'user' => $databaseUser];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Unable to add user. Error: " . $e->getMessage()];
        }
    }

    public function linkDatabaseUser($user, $database, $siteId)
    {
        $pdo = $this->pdoConnect();

        $databseUser = DatabaseUser::where('id', $user)->first();
        $database = Database::where('id', $database)->first();

        try {
            $pdo->exec("GRANT ALL PRIVILEGES ON `$database->database_name`.* TO '$databseUser->username'@'%';");
            $pdo->exec("FLUSH PRIVILEGES;");

            $link = new DatabaseUserLink();
            $link->database_id = $database->id;
            $link->database_user_id = $databseUser->id;
            $link->save();

            return ['success' => true, 'message' => 'Database and user linked successfully.', 'link' => $link];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Unable to link database and user. Error: " . $e->getMessage()];
        }
    }

       public function deleteDatabase($databaseId)
    {
        $pdo = $this->pdoConnect();

        $database = Database::find($databaseId);

        if(!$database) {
            return ['success' => false, 'message' => 'Database not found.'];
        }

        $databaseName = $database->database_name;

        try {
            $pdo->exec("DROP DATABASE IF EXISTS `$databaseName`;");
            $database->delete();
            return ['success' => true, 'message' => 'Database deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Unable to delete database. Error: " . $e->getMessage()];
        }
    }

    public function deleteUser($userId)
    {
        $pdo = $this->pdoConnect();

        $databaseUser = DatabaseUser::find($userId);

        if(!$databaseUser) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $username = $databaseUser->username;

        try {
            $pdo->exec("DROP USER '$username'@'%';");
            $pdo->exec("FLUSH PRIVILEGES;");
            $databaseUser->delete();
            return ['success' => true, 'message' => 'User deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Unable to delete user. Error: " . $e->getMessage()];
        }
    }

    public function unlinkDatabaseUser($linkId)
    {
        $pdo = $this->pdoConnect();

        $link = DatabaseUserLink::find($linkId);

        if(!$link) {
            return ['success' => false, 'message' => 'Link not found.'];
        }

        $userName = $link->databaseUser->username;
        $dbName = $link->database->database_name;

        try {
            $pdo->exec("REVOKE ALL PRIVILEGES ON `$dbName`.* FROM '$userName'@'%';");
            $pdo->exec("FLUSH PRIVILEGES;");
            $link->delete();
            return ['success' => true, 'message' => 'Database and user unlinked successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Unable to unlink database and user. Error: " . $e->getMessage()];
        }
    }
}

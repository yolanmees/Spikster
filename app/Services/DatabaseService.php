<?php

namespace App\Services;

use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\DatabaseUserLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;

class DatabaseService
{
    /** Allowed chars for database names and usernames: letters, digits, underscore, max 64. */
    protected function validateIdentifier(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z][a-zA-Z0-9_]{1,63}$/', $name);
    }

    protected function pdoConnect(): PDO
    {
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $user = config('database.connections.mysql.username', 'spikster');
        $password = config('database.connections.mysql.password', '');

        try {
            return new PDO("mysql:host={$host};port={$port}", $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('DB connection failed: '.$e->getMessage());
        }
    }

    public function createDatabase(string $databaseName, $siteId): array
    {
        if (! $this->validateIdentifier($databaseName)) {
            return ['success' => false, 'message' => 'Invalid database name.'];
        }

        $pdo = $this->pdoConnect();

        try {
            // Backtick-quoted identifiers are safe after validateIdentifier()
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}`;");

            $database = new Database;
            $database->user_id = Auth::id();
            $database->database_name = $databaseName;
            $database->site_id = $siteId;
            $database->save();

            return ['success' => true, 'message' => 'Database created.', 'database' => $database];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Unable to create database: '.$e->getMessage()];
        }
    }

    public function createUser(string $username, string $password, $siteId): array
    {
        if (! $this->validateIdentifier($username)) {
            return ['success' => false, 'message' => 'Invalid username.'];
        }

        $pdo = $this->pdoConnect();

        try {
            // Prepared statement for the existence check
            $stmt = $pdo->prepare('SELECT user FROM mysql.user WHERE user = ?');
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => "User '{$username}' already exists."];
            }

            // CREATE USER requires identifier quoting; password is a value → use prepared stmt
            $pdo->prepare("CREATE USER ?@'localhost' IDENTIFIED BY ?")->execute([$username, $password]);
            $pdo->exec('FLUSH PRIVILEGES;');

            $databaseUser = new DatabaseUser;
            $databaseUser->user_id = Auth::id();
            $databaseUser->username = $username;
            $databaseUser->password = Hash::make($password);
            $databaseUser->site_id = $siteId;
            $databaseUser->save();

            return ['success' => true, 'message' => 'User added.', 'user' => $databaseUser];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Unable to add user: '.$e->getMessage()];
        }
    }

    public function linkDatabaseUser($userId, $databaseId, $siteId): array
    {
        $pdo = $this->pdoConnect();
        $databaseUser = DatabaseUser::where('id', $userId)->where('site_id', $siteId)->first();
        $database = Database::where('id', $databaseId)->where('site_id', $siteId)->first();

        if (! $databaseUser || ! $database) {
            return ['success' => false, 'message' => 'User or database not found.'];
        }

        // Both names have already been validated on creation
        try {
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$database->database_name}`.* TO '{$databaseUser->username}'@'localhost';");
            $pdo->exec('FLUSH PRIVILEGES;');

            $link = new DatabaseUserLink;
            $link->database_id = $database->id;
            $link->database_user_id = $databaseUser->id;
            $link->save();

            return ['success' => true, 'message' => 'Linked.', 'link' => $link];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Unable to link: '.$e->getMessage()];
        }
    }

    public function deleteDatabase($databaseId, $siteId): array
    {
        $pdo = $this->pdoConnect();
        $database = Database::where('id', $databaseId)->where('site_id', $siteId)->first();

        if (! $database) {
            return ['success' => false, 'message' => 'Database not found.'];
        }

        try {
            $pdo->exec("DROP DATABASE IF EXISTS `{$database->database_name}`;");
            $database->delete();

            return ['success' => true, 'message' => 'Database deleted.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Unable to delete database: '.$e->getMessage()];
        }
    }

    public function deleteUser($userId, $siteId): array
    {
        $pdo = $this->pdoConnect();
        $databaseUser = DatabaseUser::where('id', $userId)->where('site_id', $siteId)->first();

        if (! $databaseUser) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        try {
            $pdo->prepare("DROP USER ?@'localhost'")->execute([$databaseUser->username]);
            $pdo->exec('FLUSH PRIVILEGES;');
            $databaseUser->delete();

            return ['success' => true, 'message' => 'User deleted.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Unable to delete user: '.$e->getMessage()];
        }
    }

    public function unlinkDatabaseUser($linkId, $siteId): array
    {
        $pdo = $this->pdoConnect();
        $link = DatabaseUserLink::with(['database', 'databaseUser'])->find($linkId);

        if (! $link) {
            return ['success' => false, 'message' => 'Link not found.'];
        }

        // Ownership check: ensure both sides of the link belong to this site
        if (
            ($link->database && $link->database->site_id != $siteId) ||
            ($link->databaseUser && $link->databaseUser->site_id != $siteId)
        ) {
            return ['success' => false, 'message' => 'Link not found.'];
        }

        if (! $link->database || ! $link->databaseUser) {
            $link->delete(); // orphaned link — clean it up

            return ['success' => true, 'message' => 'Orphaned link removed.'];
        }

        try {
            $pdo->exec("REVOKE ALL PRIVILEGES ON `{$link->database->database_name}`.* FROM '{$link->databaseUser->username}'@'localhost';");
            $pdo->exec('FLUSH PRIVILEGES;');
            $link->delete();

            return ['success' => true, 'message' => 'Unlinked.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Unable to unlink: '.$e->getMessage()];
        }
    }
}

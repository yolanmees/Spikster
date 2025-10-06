<?php

namespace App\Jobs\Ftp;

use App\Models\FtpUser;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateFtpUserSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;
    public $tries = 3;

    public function __construct(
        public FtpUser $ftpUser
    ) {}

    public function handle(): void
    {
        $ssh = new SSHService($this->ftpUser->server);

        try {
            Log::info("Updating FTP user on server", [
                'username' => $this->ftpUser->username,
            ]);

            // Update virtual user database (in case password changed)
            $this->updateVirtualUser($ssh);

            // Update user-specific configuration
            $this->updateUserConfig($ssh);

            // Update chroot directory permissions if needed
            $this->updateChrootDirectory($ssh);

            // Reload vsftpd
            $ssh->execute('systemctl reload vsftpd || service vsftpd reload');

            Log::info("FTP user updated successfully", [
                'username' => $this->ftpUser->username,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update FTP user", [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function updateVirtualUser(SSHService $ssh): void
    {
        $username = $this->ftpUser->username;
        $password = $this->generateRandomPassword();

        // Remove old entry
        $ssh->execute("sed -i '/{$username}/,+1d' /etc/vsftpd/vusers.txt");

        // Add updated entry
        $ssh->execute("echo '{$username}' >> /etc/vsftpd/vusers.txt");
        $ssh->execute("echo '{$password}' >> /etc/vsftpd/vusers.txt");

        // Regenerate Berkeley DB
        $ssh->execute('db_load -T -t hash -f /etc/vsftpd/vusers.txt /etc/vsftpd/vusers.db');
        $ssh->execute('chmod 600 /etc/vsftpd/vusers.db');
    }

    protected function updateUserConfig(SSHService $ssh): void
    {
        $username = $this->ftpUser->username;
        $homeDir = $this->ftpUser->home_directory;
        $maxConn = $this->ftpUser->max_connections;

        $permissions = $this->ftpUser->permissions;
        $writeEnable = ($permissions['write'] ?? true) ? 'YES' : 'NO';
        $deleteEnable = ($permissions['delete'] ?? true) ? 'YES' : 'NO';

        $config = "local_root={$homeDir}\n";
        $config .= "write_enable={$writeEnable}\n";
        $config .= "anon_upload_enable={$writeEnable}\n";
        $config .= "anon_mkdir_write_enable={$writeEnable}\n";
        $config .= "anon_other_write_enable={$deleteEnable}\n";
        $config .= "max_clients={$maxConn}\n";
        $config .= "max_per_ip={$maxConn}\n";

        if ($this->ftpUser->bandwidth_limit_kbps) {
            $bytesPerSecond = $this->ftpUser->bandwidth_limit_kbps * 1024;
            $config .= "local_max_rate={$bytesPerSecond}\n";
        }

        // If user is inactive, deny login
        if (!$this->ftpUser->is_active) {
            $config .= "deny_file=*\n";
        }

        $ssh->execute("cat > /etc/vsftpd/users/{$username} << 'USER_EOF'\n{$config}\nUSER_EOF");
    }

    protected function updateChrootDirectory(SSHService $ssh): void
    {
        $homeDir = $this->ftpUser->home_directory;

        // Ensure directory exists
        $ssh->execute("mkdir -p {$homeDir}");

        // Update ownership
        $ssh->execute("chown -R www-data:www-data {$homeDir}");

        // Update permissions
        $ssh->execute("chmod 755 {$homeDir}");
    }

    protected function generateRandomPassword(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}

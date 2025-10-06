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

class DeleteFtpUserSSH implements ShouldQueue
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
            Log::info("Deleting FTP user from server", [
                'username' => $this->ftpUser->username,
            ]);

            // Remove from virtual user database
            $this->removeVirtualUser($ssh);

            // Remove user-specific configuration
            $this->removeUserConfig($ssh);

            // Optionally remove chroot directory (commented out for safety)
            // $this->removeChroot Directory($ssh);

            // Reload vsftpd
            $ssh->execute('systemctl reload vsftpd || service vsftpd reload');

            Log::info("FTP user deleted successfully", [
                'username' => $this->ftpUser->username,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to delete FTP user", [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function removeVirtualUser(SSHService $ssh): void
    {
        $username = $this->ftpUser->username;

        // Remove user from vusers.txt (user line + password line)
        $ssh->execute("sed -i '/{$username}/,+1d' /etc/vsftpd/vusers.txt");

        // Regenerate Berkeley DB
        $ssh->execute('db_load -T -t hash -f /etc/vsftpd/vusers.txt /etc/vsftpd/vusers.db');
        $ssh->execute('chmod 600 /etc/vsftpd/vusers.db');

        Log::info("Removed FTP user from virtual user database", [
            'username' => $username,
        ]);
    }

    protected function removeUserConfig(SSHService $ssh): void
    {
        $username = $this->ftpUser->username;

        // Remove user-specific config file
        $ssh->execute("rm -f /etc/vsftpd/users/{$username}");

        Log::info("Removed FTP user config", [
            'username' => $username,
        ]);
    }

    protected function removeChrootDirectory(SSHService $ssh): void
    {
        // CAUTION: This removes the entire home directory
        // Only uncomment if you're sure you want to delete user files

        // $homeDir = $this->ftpUser->home_directory;
        // $ssh->execute("rm -rf {$homeDir}");

        // Log::info("Removed chroot directory", [
        //     'home_directory' => $homeDir,
        // ]);
    }
}

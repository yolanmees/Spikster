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

class UpdateFtpQuotaSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(
        public FtpUser $ftpUser
    ) {}

    public function handle(): void
    {
        $ssh = new SSHService($this->ftpUser->server);

        try {
            Log::info("Updating FTP user quota on server", [
                'username' => $this->ftpUser->username,
                'quota_mb' => $this->ftpUser->quota_mb,
            ]);

            // Update quota file
            $this->updateQuotaFile($ssh);

            Log::info("FTP user quota updated successfully", [
                'username' => $this->ftpUser->username,
                'quota_mb' => $this->ftpUser->quota_mb,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update FTP user quota", [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function updateQuotaFile(SSHService $ssh): void
    {
        $homeDir = $this->ftpUser->home_directory;
        $quotaBytes = $this->ftpUser->quota_mb * 1024 * 1024;

        // Update .ftpquota file
        $ssh->execute("echo '{$quotaBytes}' > {$homeDir}/.ftpquota");
        $ssh->execute("chown www-data:www-data {$homeDir}/.ftpquota");

        Log::info("Updated quota file", [
            'quota_file' => "{$homeDir}/.ftpquota",
            'quota_bytes' => $quotaBytes,
        ]);
    }
}

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

class UpdateDiskUsageSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 2;

    public function __construct(
        public FtpUser $ftpUser
    ) {}

    public function handle(): void
    {
        $ssh = new SSHService($this->ftpUser->server);

        try {
            $homeDir = $this->ftpUser->home_directory;

            // Get disk usage in bytes
            $usage = $ssh->execute("du -sb {$homeDir} | awk '{print $1}'");
            $usageBytes = (int) trim($usage);

            // Update database
            $this->ftpUser->updateDiskUsage($usageBytes);

            Log::info("Updated FTP user disk usage", [
                'username' => $this->ftpUser->username,
                'usage_bytes' => $usageBytes,
                'usage_mb' => round($usageBytes / (1024 * 1024), 2),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update FTP user disk usage", [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - this is not critical
        }
    }
}

<?php

namespace App\Jobs\Ftp;

use App\Models\FtpUser;
use App\Services\RemoteDaemonService;
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

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            $result = $daemon->send($this->ftpUser->server, 'ftp.disk-usage', [
                'username' => $this->ftpUser->username,
                'home_directory' => $this->ftpUser->home_directory,
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for ftp.disk-usage');
            }

            $usageBytes = (int) ($result['usage_bytes'] ?? 0);
            $this->ftpUser->updateDiskUsage($usageBytes);

            Log::info('Updated FTP user disk usage via daemon', [
                'username' => $this->ftpUser->username,
                'usage_bytes' => $usageBytes,
                'usage_mb' => round($usageBytes / (1024 * 1024), 2),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update FTP user disk usage', [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
            ]);
            // Non-critical: don't rethrow
        }
    }
}

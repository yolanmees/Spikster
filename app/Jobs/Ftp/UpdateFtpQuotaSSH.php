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

class UpdateFtpQuotaSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function __construct(
        public FtpUser $ftpUser
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            Log::info("Updating FTP user quota via daemon", [
                'username' => $this->ftpUser->username,
                'quota_mb' => $this->ftpUser->quota_mb,
            ]);

            $result = $daemon->send($this->ftpUser->server, 'ftp.update-quota', [
                'username'       => $this->ftpUser->username,
                'home_directory' => $this->ftpUser->home_directory,
                'quota_mb'       => $this->ftpUser->quota_mb,
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for ftp.update-quota');
            }

            Log::info("FTP user quota updated successfully via daemon", [
                'username' => $this->ftpUser->username,
                'quota_mb' => $this->ftpUser->quota_mb,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update FTP user quota", [
                'username' => $this->ftpUser->username,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("UpdateFtpQuotaSSH job failed", [
            'username' => $this->ftpUser->username,
            'error'    => $exception->getMessage(),
        ]);
    }
}

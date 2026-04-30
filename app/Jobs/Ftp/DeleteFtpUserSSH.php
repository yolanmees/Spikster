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

class DeleteFtpUserSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;
    public $tries = 3;

    public function __construct(
        public FtpUser $ftpUser
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            Log::info("Deleting FTP user via daemon", ['username' => $this->ftpUser->username]);

            $result = $daemon->send($this->ftpUser->server, 'ftp.delete', [
                'username' => $this->ftpUser->username,
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for ftp.delete');
            }

            Log::info("FTP user deleted successfully via daemon", ['username' => $this->ftpUser->username]);

        } catch (\Exception $e) {
            Log::error("Failed to delete FTP user", [
                'username' => $this->ftpUser->username,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("DeleteFtpUserSSH job failed", [
            'username' => $this->ftpUser->username,
            'error'    => $exception->getMessage(),
        ]);
    }
}

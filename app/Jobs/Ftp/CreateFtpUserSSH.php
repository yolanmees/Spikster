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

class CreateFtpUserSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public function __construct(
        public FtpUser $ftpUser
    ) {}

    public function handle(RemoteDaemonService $daemon): void
    {
        try {
            Log::info('Creating FTP user via daemon', [
                'username' => $this->ftpUser->username,
                'server_id' => $this->ftpUser->server_id,
            ]);

            $result = $daemon->send($this->ftpUser->server, 'ftp.create', [
                'username' => $this->ftpUser->username,
                'home_directory' => $this->ftpUser->home_directory,
                'quota_mb' => $this->ftpUser->quota_mb,
                'bandwidth_limit_kbps' => $this->ftpUser->bandwidth_limit_kbps,
                'max_connections' => $this->ftpUser->max_connections,
                'permissions' => $this->ftpUser->permissions,
                'allowed_ip' => $this->ftpUser->allowed_ip,
            ]);

            if (empty($result['success'])) {
                throw new \Exception($result['error'] ?? 'Daemon returned failure for ftp.create');
            }

            Log::info('FTP user created successfully via daemon', [
                'username' => $this->ftpUser->username,
                'home_directory' => $this->ftpUser->home_directory,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create FTP user', [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CreateFtpUserSSH job failed', [
            'username' => $this->ftpUser->username,
            'error' => $exception->getMessage(),
        ]);
    }
}

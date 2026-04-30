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

/**
 * Test FTP connectivity for a virtual user.
 *
 * Delegates to the daemon (ftp.test action) so the connection test runs on
 * the same host as vsftpd, avoiding firewall/NAT issues from the panel server.
 * The plain-text password is passed only at dispatch time and not stored here.
 */
class TestFtpConnectionSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 1;

    public function __construct(
        public FtpUser $ftpUser,
        public string $password
    ) {}

    public function handle(RemoteDaemonService $daemon): array
    {
        try {
            Log::info("Testing FTP connection via daemon", ['username' => $this->ftpUser->username]);

            $result = $daemon->send($this->ftpUser->server, 'ftp.test', [
                'username' => $this->ftpUser->username,
                'password' => $this->password,
                'host'     => '127.0.0.1',
            ]);

            $success = $result['success'] ?? false;

            Log::info("FTP connection test completed via daemon", [
                'username' => $this->ftpUser->username,
                'success'  => $success,
            ]);

            return [
                'success' => $success,
                'message' => $result['message'] ?? ($success ? 'FTP connection successful' : 'FTP connection failed'),
            ];

        } catch (\Exception $e) {
            Log::error("FTP connection test failed", [
                'username' => $this->ftpUser->username,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }
}

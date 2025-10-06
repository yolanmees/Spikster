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

class TestFtpConnectionSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 1;

    public function __construct(
        public FtpUser $ftpUser,
        public string $password
    ) {}

    public function handle(): array
    {
        $ssh = new SSHService($this->ftpUser->server);

        try {
            Log::info("Testing FTP connection", [
                'username' => $this->ftpUser->username,
            ]);

            $result = $this->testConnection($ssh);

            Log::info("FTP connection test completed", [
                'username' => $this->ftpUser->username,
                'success' => $result['success'],
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error("FTP connection test failed", [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }

    protected function testConnection(SSHService $ssh): array
    {
        $host = $this->ftpUser->server->ip;
        $username = $this->ftpUser->username;
        $password = $this->password;

        // Test using lftp
        $testScript = <<<BASH
lftp -u "{$username},{$password}" -e "pwd; exit" {$host} 2>&1
BASH;

        $output = $ssh->execute($testScript);

        // Check if connection was successful
        if (str_contains($output, $this->ftpUser->home_directory) ||
            str_contains(strtolower($output), 'logged in')) {
            return [
                'success' => true,
                'message' => 'FTP connection successful',
                'output' => $output,
            ];
        }

        return [
            'success' => false,
            'message' => 'FTP connection failed',
            'output' => $output,
        ];
    }
}

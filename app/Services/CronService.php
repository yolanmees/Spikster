<?php

namespace App\Services;

use App\Models\CronJob;
use App\Models\Server;
use phpseclib3\Net\SSH2;

class CronService
{
    /**
     * Sync all cron jobs from database to server crontab
     */
    public function syncCronJobsToServer(Server $server): void
    {
        // Get all enabled cron jobs for this server
        $cronJobs = $server->cronJobs()->get();

        // Build crontab content
        $crontabContent = $this->buildCrontabContent($cronJobs);

        // Connect to server and update crontab
        $this->updateServerCrontab($server, $crontabContent);
    }

    /**
     * Build crontab file content from cron jobs
     */
    protected function buildCrontabContent($cronJobs): string
    {
        $content = "# Spikster Managed Cron Jobs\n";
        $content .= '# Generated: '.now()->toDateTimeString()."\n\n";

        foreach ($cronJobs as $cronJob) {
            // Add description as comment
            if ($cronJob->description) {
                $content .= "# {$cronJob->description}\n";
            }

            // Add the cron line
            $content .= $cronJob->cron_line."\n\n";
        }

        return $content;
    }

    /**
     * Update crontab on the server via SSH
     */
    protected function updateServerCrontab(Server $server, string $content): void
    {
        $ssh = new SSH2($server->ip);

        if (! $ssh->login('spikster', $server->password)) {
            throw new \Exception('SSH authentication failed');
        }

        // Create temporary file with crontab content
        $tempFile = '/tmp/spikster_crontab_'.uniqid();

        // Write content to temp file
        $ssh->exec("cat > {$tempFile} << 'EOL'\n{$content}\nEOL");

        // Install the crontab
        $result = $ssh->exec("crontab {$tempFile}");

        // Clean up temp file
        $ssh->exec("rm -f {$tempFile}");

        // Verify installation
        $currentCrontab = $ssh->exec('crontab -l');

        if (strpos($currentCrontab, '# Spikster Managed Cron Jobs') === false) {
            throw new \Exception('Failed to update crontab on server');
        }

        $ssh->disconnect();
    }

    /**
     * Get current crontab from server
     */
    public function getCurrentCrontab(Server $server): string
    {
        $ssh = new SSH2($server->ip);

        if (! $ssh->login('spikster', $server->password)) {
            throw new \Exception('SSH authentication failed');
        }

        $crontab = $ssh->exec('crontab -l 2>/dev/null');
        $ssh->disconnect();

        return $crontab ?: '';
    }

    /**
     * Parse crontab content and import as cron jobs
     */
    public function importFromServer(Server $server): int
    {
        $crontab = $this->getCurrentCrontab($server);

        if (empty($crontab)) {
            return 0;
        }

        $lines = explode("\n", $crontab);
        $imported = 0;
        $description = null;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and Spikster managed header
            if (empty($line) || strpos($line, '# Spikster Managed') === 0 || strpos($line, '# Generated:') === 0) {
                continue;
            }

            // Check if it's a comment (potential description)
            if (strpos($line, '#') === 0) {
                $description = trim(substr($line, 1));

                continue;
            }

            // Parse cron line
            $parts = preg_split('/\s+/', $line, 6);

            if (count($parts) >= 6) {
                $schedule = implode(' ', array_slice($parts, 0, 5));
                $command = $parts[5];

                // Remove output redirection if present
                $command = preg_replace('/\s*>>?\s*[^\s]+\s*2>&1\s*$/', '', $command);

                // Check if already exists
                $exists = CronJob::where('server_id', $server->id)
                    ->where('command', $command)
                    ->where('schedule', $schedule)
                    ->exists();

                if (! $exists) {
                    CronJob::create([
                        'server_id' => $server->id,
                        'command' => $command,
                        'schedule' => $schedule,
                        'description' => $description,
                        'enabled' => strpos($line, '#') !== 0,
                    ]);
                    $imported++;
                }

                $description = null; // Reset description
            }
        }

        return $imported;
    }

    /**
     * Clear all cron jobs from server crontab
     */
    public function clearServerCrontab(Server $server): void
    {
        $ssh = new SSH2($server->ip);

        if (! $ssh->login('spikster', $server->password)) {
            throw new \Exception('SSH authentication failed');
        }

        // Remove crontab
        $ssh->exec('crontab -r 2>/dev/null');
        $ssh->disconnect();
    }
}

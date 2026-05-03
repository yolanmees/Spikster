<?php

namespace App\Services;

use App\Models\CronJob;
use App\Models\Server;

class CronService
{
    public function __construct(
        protected RemoteDaemonService $daemon
    ) {}

    /**
     * Sync all cron jobs from database to server crontab.
     */
    public function syncCronJobsToServer(Server $server): void
    {
        $cronJobs = $server->cronJobs()->get();
        $crontabContent = $this->buildCrontabContent($cronJobs);

        $result = $this->daemon->send($server, 'cron.write', ['content' => $crontabContent]);

        if (! ($result['success'] ?? false)) {
            throw new \Exception('Failed to update crontab on server: '.($result['error'] ?? 'unknown error'));
        }
    }

    /**
     * Build crontab file content from cron jobs.
     */
    protected function buildCrontabContent($cronJobs): string
    {
        $content = "# Spikster Managed Cron Jobs\n";
        $content .= '# Generated: '.now()->toDateTimeString()."\n\n";

        foreach ($cronJobs as $cronJob) {
            if ($cronJob->description) {
                $content .= "# {$cronJob->description}\n";
            }

            $content .= $cronJob->cron_line."\n\n";
        }

        return $content;
    }

    /**
     * Get current crontab from server via daemon.
     */
    public function getCurrentCrontab(Server $server): string
    {
        $result = $this->daemon->send($server, 'cron.read', []);

        if (! ($result['success'] ?? false)) {
            throw new \Exception('Failed to read crontab: '.($result['error'] ?? 'unknown error'));
        }

        return $result['output'] ?? '';
    }

    /**
     * Parse crontab content and import as cron jobs.
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

            if (empty($line) || strpos($line, '# Spikster Managed') === 0 || strpos($line, '# Generated:') === 0) {
                continue;
            }

            if (strpos($line, '#') === 0) {
                $description = trim(substr($line, 1));

                continue;
            }

            $parts = preg_split('/\s+/', $line, 6);

            if (count($parts) >= 6) {
                $schedule = implode(' ', array_slice($parts, 0, 5));
                $command = $parts[5];

                $command = preg_replace('/\s*>>?\s*[^\s]+\s*2>&1\s*$/', '', $command);

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

                $description = null;
            }
        }

        return $imported;
    }

    /**
     * Clear all cron jobs from server crontab.
     */
    public function clearServerCrontab(Server $server): void
    {
        $this->daemon->send($server, 'cron.write', ['content' => '']);
    }
}

<?php

namespace App\Jobs;

use App\Models\Database;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DatabaseExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        protected Database $database,
        protected Site $site,
        protected string $format = 'sql'
    ) {}

    public function handle(): void
    {
        $this->database->update(['export_status' => 'running']);

        try {
            $filename = "{$this->site->domain}_{$this->database->database_name}_".now()->format('Ymd_His').".{$this->format}.gz";
            $path = storage_path("app/exports/{$filename}");
            $dir = dirname($path);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if ($this->format === 'sql') {
                $command = sprintf(
                    'mysqldump --single-transaction --quick %s | gzip > %s',
                    escapeshellarg($this->database->database_name),
                    escapeshellarg($path)
                );
            } else {
                throw new \InvalidArgumentException("Unsupported format: {$this->format}");
            }

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \RuntimeException('Database export failed');
            }

            $this->database->update([
                'export_status' => 'completed',
                'export_path' => $path,
                'export_size' => file_exists($path) ? filesize($path) : 0,
                'exported_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->database->update([
                'export_status' => 'failed',
                'export_error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

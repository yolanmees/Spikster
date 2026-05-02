<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use App\Services\DaemonService;
use Illuminate\Support\Facades\DB;

class DiagnosticsController extends Controller
{
    public function index(DaemonService $daemon)
    {
        $daemonOk = false;
        $daemonError = null;
        try {
            $daemonOk = $daemon->isAvailable();
        } catch (\Throwable $e) {
            $daemonError = $e->getMessage();
        }

        $dbDriver = DB::connection()->getDriverName();
        $failedJobs = DB::table('failed_jobs')->count();
        $pendingJobs = DB::table('jobs')->count();

        try {
            $dbSize = DB::select('SELECT page_count * page_size AS size FROM pragma_page_size, pragma_page_count');
            $dbSizeBytes = $dbSize[0]->size ?? 0;
        } catch (\Throwable) {
            $dbSizeBytes = 0;
        }

        $logSize = 0;
        $logDir = storage_path('logs');
        if (is_dir($logDir)) {
            foreach (new \DirectoryIterator($logDir) as $file) {
                if ($file->isFile() && $file->getExtension() === 'log') {
                    $logSize += $file->getSize();
                }
            }
        }

        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'app_url' => config('app.url'),
            'db_driver' => $dbDriver,
            'db_size' => $this->formatBytes($dbSizeBytes),
            'queue_driver' => config('queue.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'log_channel' => config('logging.default'),
            'log_size' => $this->formatBytes($logSize),

            'servers_total' => Server::count(),
            'servers_active' => Server::where('status', 1)->count(),
            'sites_total' => Site::count(),
            'sites_panel' => Site::where('panel', true)->count(),
            'users_total' => User::count(),
            'deployments_total' => Deployment::count(),

            'failed_jobs' => $failedJobs,
            'pending_jobs' => $pendingJobs,
            'daemon_running' => $daemonOk,
            'daemon_error' => $daemonError,

            'disk_free' => $this->formatBytes(disk_free_space(base_path())),
            'disk_total' => $this->formatBytes(disk_total_space(base_path())),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time'),
            'timezone' => config('app.timezone'),

            'php_extensions' => implode(', ', get_loaded_extensions()),
        ];

        return view('diagnostics.index', compact('info'));
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}

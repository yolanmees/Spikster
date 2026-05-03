<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogManagerController extends Controller
{
    //

    public function index(): JsonResponse
    {
        $logs = [];
        $log_files = glob(storage_path('logs/*.log'));
        foreach ($log_files as $file) {
            $logs[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file),
            ];
        }
        // also get the logs inside of subfolders
        $server_logs = [];
        $server_log_files = glob(storage_path('server_logs/*.log'));
        foreach ($server_log_files as $file) {
            $server_logs[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file),
            ];
        }
        $server_log_files = glob(storage_path('server_logs/*/*.log'));
        foreach ($server_log_files as $file) {
            $server_logs[] = [
                'name' => basename(dirname($file)).'_'.basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file),
            ];
        }

        return response()->json([
            'logs' => $logs,
            'server_logs' => $server_logs,
        ]);
    }

    public function show($log): JsonResponse
    {
        $originalLog = $log;
        // Only allow log files, not arbitrary paths
        $log = $this->resolveLogPath($log);

        if ($log === null) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        $content = file_get_contents($log);

        return response()->json([
            'name' => $originalLog,
            'log' => $content,
        ]);
    }

    public function download($log): BinaryFileResponse|JsonResponse
    {
        $log = $this->resolveLogPath($log);

        if ($log === null) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        return response()->download($log);
    }

    public function delete($log): JsonResponse
    {
        // Only allow deleting application logs, not server logs for safety
        if (strpos($log, '_') !== false) {
            return response()->json([
                'error' => 'Cannot delete server logs',
            ], 403);
        }

        $filePath = storage_path('logs/'.$log);

        if (file_exists($filePath)) {
            unlink($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Log file deleted successfully',
            ]);
        } else {
            return response()->json([
                'error' => 'Log not found',
            ], 404);
        }
    }

    /**
     * Resolve a log name to a safe absolute path within the logs directory.
     */
    private function resolveLogPath(string $log): ?string
    {
        // Block path traversal attempts
        if (str_contains($log, '..') || str_contains($log, "\0")) {
            return null;
        }

        // Allow underscores that represent subdirectory separators (from index listing)
        if (str_contains($log, '_')) {
            $log = str_replace('_', '/', $log);
        }

        // Build candidate paths
        $candidates = [
            storage_path('logs/'.$log),
            storage_path('server_logs/'.$log),
        ];

        foreach ($candidates as $candidate) {
            $realPath = realpath($candidate);
            if ($realPath === false) {
                continue;
            }
            // Ensure resolved path is within the logs directory
            if (str_starts_with($realPath, storage_path('logs'))
                || str_starts_with($realPath, storage_path('server_logs'))) {
                return $realPath;
            }
        }

        return null;
    }
}

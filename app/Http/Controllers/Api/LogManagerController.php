<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class LogManagerController extends Controller
{
    //

    public function index(): \Illuminate\Http\JsonResponse
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

    public function show($log): \Illuminate\Http\JsonResponse
    {
        $originalLog = $log;
        // check if log contains a underscore and if so, replace it with a slash
        if (strpos($log, '_') !== false) {
            $log = str_replace('_', '/', $log);
        }
        if (file_exists(storage_path('logs/'.$log))) {
            $content = file_get_contents(storage_path('logs/'.$log));

            return response()->json([
                'name' => $originalLog,
                'log' => $content,
            ]);
        } elseif (file_exists(storage_path('server_logs/'.$log))) {
            $content = file_get_contents(storage_path('server_logs/'.$log));

            return response()->json([
                'name' => $originalLog,
                'log' => $content,
            ]);
        } else {
            dd($log);

            return response()->json([
                'error' => 'Log not found',
            ], 404);
        }

    }

    public function download($log): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        // check if log contains a underscore and if so, replace it with a slash
        if (strpos($log, '_') !== false) {
            $log = str_replace('_', '/', $log);
        }

        if (file_exists(storage_path('logs/'.$log))) {
            return response()->download(storage_path('logs/'.$log));
        } elseif (file_exists(storage_path('server_logs/'.$log))) {
            return response()->download(storage_path('server_logs/'.$log));
        } else {
            return response()->json([
                'error' => 'Log not found',
            ], 404);
        }
    }

    public function delete($log): \Illuminate\Http\JsonResponse
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
}

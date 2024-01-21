<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogManagerController extends Controller
{
    //

    public function index(): \Illuminate\Http\JsonResponse
    {
        $logs = [];
        $log_files = glob(storage_path('logs/*.log'));
        foreach ($log_files as $file) {
            $logs[] = basename($file);
        }
        // also get the logs inside of subfolders 
        $server_logs = [];
        $server_log_files = glob(storage_path('server_logs/*.log'));
        foreach ($server_log_files as $file) {
            $server_logs[] = basename($file);

        }
        $server_log_files = glob(storage_path('server_logs/*/*.log'));
        foreach ($server_log_files as $file) {
            $server_logs[] = basename(dirname($file)) . '_' . basename($file);
        }


        return response()->json([
            'logs' => $logs,
            'server_logs' => $server_logs
        ]);
    }

    public function show($log): \Illuminate\Http\JsonResponse
    {
        //check if log contains a underscore and if so, replace it with a slash
        if (strpos($log, '_') !== false) {
            $log = str_replace('_', '/', $log);
        }
        if (file_exists(storage_path('logs/' . $log))) {
            $log = file_get_contents(storage_path('logs/' . $log));
            return response()->json([
                'log' => $log
            ]);
        } elseif (file_exists(storage_path('server_logs/' . $log))) {
            $log = file_get_contents(storage_path('server_logs/' . $log));
            return response()->json([
                'log' => $log
            ]);
        } else {
            dd($log);
            return response()->json([
                'error' => 'Log not found'
            ], 404);
        }
        
    }


}

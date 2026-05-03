<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\ScanResult;
use App\Models\Server;
use App\Services\RemoteDaemonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class MonitoringController extends Controller
{
    public function listServices(Request $request)
    {
        $format = $request->get('format', 'json');
        $process = new Process(['sudo', '/var/www/html/bin/spikster', 'list-services', '--format', $format]);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Error executing spikster: '.$process->getErrorOutput());

            return response()->json([
                'result' => 'error',
                'message' => 'Failed to list services',
                'details' => $process->getErrorOutput(),
            ], 500);
        }

        $output = preg_replace('/\\\\n/', ' ', $process->getOutput());

        return response($output)->header('Content-Type', 'application/json');
    }

    public function manageService(Request $request)
    {
        $allowedActions = ['start', 'stop', 'restart', 'status', 'reload'];
        $allowedFormats = ['json', 'text'];

        $action = $request->get('action');
        $service = $request->get('service');
        $format = $request->get('format', 'json');

        if (! $action || ! $service) {
            return response()->json(['result' => 'error', 'message' => 'Invalid request parameters'], 400);
        }

        if (! in_array($action, $allowedActions)) {
            return response()->json(['result' => 'error', 'message' => 'Invalid action'], 400);
        }

        if (! in_array($format, $allowedFormats)) {
            return response()->json(['result' => 'error', 'message' => 'Invalid format'], 400);
        }

        if (! preg_match('/^[a-zA-Z0-9\-\.@]+$/', $service)) {
            return response()->json(['result' => 'error', 'message' => 'Invalid service name'], 400);
        }

        $process = new Process(['bin/spikster', 'manage-services', '--format', $format, $action, $service]);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Error executing spikster: '.$process->getErrorOutput());

            return response()->json(['result' => 'error', 'message' => 'Failed to manage service'], 500);
        }

        return response()->json(json_decode($process->getOutput(), true));
    }

    public function malwareScan(string $server_id, RemoteDaemonService $daemon)
    {
        $server = Server::where('server_id', $server_id)->firstOrFail();

        try {
            $result = $daemon->malwareScan($server);

            $success = $result['success'] ?? false;
            $output = $result['output'] ?? '{}';
            $scanData = json_decode($output, true) ?: [];

            // Store in DB
            ScanResult::create([
                'server_id' => $server_id,
                'type' => 'malware',
                'status' => $scanData['summary']['status'] ?? ($success ? 'clean' : 'failed'),
                'findings' => $scanData,
                'findings_count' => array_sum([
                    $scanData['summary']['processes_found'] ?? 0,
                    $scanData['summary']['binaries_found'] ?? 0,
                    $scanData['summary']['cron_jobs_found'] ?? 0,
                    $scanData['summary']['startup_files_found'] ?? 0,
                ]),
                'scanned_by' => auth()->user()?->email ?? 'system',
                'scanned_at' => now(),
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            ScanResult::create([
                'server_id' => $server_id,
                'type' => 'malware',
                'status' => 'failed',
                'findings' => ['error' => $e->getMessage()],
                'findings_count' => 0,
                'scanned_by' => auth()->user()?->email ?? 'system',
                'scanned_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

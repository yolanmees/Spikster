<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Stats\Cpu;
use App\Models\Stats\Disk;
use App\Models\Stats\Load;
use App\Models\Stats\Mem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class MonitoringController extends Controller
{
    public function statsCpu(Server $server)
    {
        $cpu = Cpu::orderBy('created_at', 'desc')->paginate(50)->sortBy('created_at');

        if ($cpu->count() > 0) {
            return response()->json(['cpu' => $cpu]);
        }

        return response()->json([
            'message' => __('spikster.something_error_message'),
            'errors' => __('spikster.error'),
        ], 500);
    }

    public function statsMem(Server $server)
    {
        $mem = Mem::orderBy('created_at', 'desc')->paginate(50)->sortBy('created_at');

        if ($mem->count() > 0) {
            return response()->json(['mem' => $mem]);
        }

        return response()->json([
            'message' => __('spikster.something_error_message'),
            'errors' => __('spikster.error'),
        ], 500);
    }

    public function statsLoad(Server $server)
    {
        $load = Load::orderBy('created_at', 'desc')->paginate(50)->sortBy('created_at');

        if ($load->count() > 0) {
            return response()->json(['load' => $load]);
        }

        return response()->json([
            'message' => __('spikster.something_error_message'),
            'errors' => __('spikster.error'),
        ], 500);
    }

    public function statsDisk(Server $server)
    {
        $disk = Disk::orderBy('created_at', 'desc')->paginate(50)->sortBy('created_at');

        if ($disk->count() > 0) {
            return response()->json(['disk' => $disk]);
        }

        return response()->json([
            'message' => __('spikster.something_error_message'),
            'errors' => __('spikster.error'),
        ], 500);
    }

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
}

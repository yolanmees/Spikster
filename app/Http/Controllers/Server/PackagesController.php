<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\DaemonService;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    public function index(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        try {
            $result = app(DaemonService::class)->send('server.package-list', []);
            $packages = $result['output'] ?? '';
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }

        $packages = explode("\n", $packages);
        foreach ($packages as $i => $package) {
            if ($package == '') {
                unset($packages[$i]);
            } else {
                $k = 0;
                $packages[$i] = explode("\t", $package);
                foreach ($packages[$i] as $j => $item) {
                    if ($item == '') {
                        unset($packages[$i][$j]);
                    } else {
                        if ($k == 0) {
                            $packages[$i]['package'] = $item;
                        } elseif ($k == 1) {
                            $packages[$i]['status'] = $item;
                        }
                        unset($packages[$i][$j]);
                        $k++;
                    }
                }
            }
        }

        return response()->json([$packages]);
    }

    public function install(string $server_id, Request $request)
    {
        Server::where('server_id', $server_id)->where('status', 1)->firstOrFail();
        $package = $request->package;

        try {
            app(DaemonService::class)->send('server.package-install', ['package' => $package]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }

        return response()->json([]);
    }

    public function uninstall(string $server_id, Request $request)
    {
        Server::where('server_id', $server_id)->where('status', 1)->firstOrFail();
        $package = $request->package;

        try {
            app(DaemonService::class)->send('server.package-remove', ['package' => $package]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }

        return response()->json([]);
    }
}

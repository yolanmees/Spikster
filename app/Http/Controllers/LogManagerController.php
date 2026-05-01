<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LogManagerController extends Controller
{
    public function index($server_id): View
    {
        $server = Server::where(['server_id' => $server_id])->first();
        $server_url = 'http://'.$server->ip.'/api/logs';
        $logs = json_decode(file_get_contents($server_url), true);

        return view('server.logs.index', compact('server', 'logs'));
    }

    public function show($server_id, $log): View
    {
        $server = Server::where(['server_id' => $server_id])->first();
        $server_url = 'http://'.$server->ip.'/api/logs/'.$log;
        $log = json_decode(file_get_contents($server_url), true);

        return view('server.logs.show', compact('server', 'log'));
    }

    public function download($server_id, $log)
    {
        $server = Server::where(['server_id' => $server_id])->first();
        $server_url = 'http://'.$server->ip.'/api/logs/'.$log.'/download';

        // Stream the file from the remote server
        $content = file_get_contents($server_url);
        $filename = str_replace('_', '_', $log);

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function delete($server_id, $log): RedirectResponse
    {
        $server = Server::where(['server_id' => $server_id])->first();
        $server_url = 'http://'.$server->ip.'/api/logs/'.$log;

        // Create a DELETE request
        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'header' => 'Content-Type: application/json',
            ],
        ]);

        $result = file_get_contents($server_url, false, $context);

        return redirect()->route('logs.index', ['server_id' => $server_id])
            ->with('success', 'Log file deleted successfully');
    }
}

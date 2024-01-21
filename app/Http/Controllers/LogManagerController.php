<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;

class LogManagerController extends Controller
{
    public function index($server_id): \Illuminate\View\View
    {
        $server = Server::where(['server_id' => $server_id])->first();
        $server_url = "http://". $server->ip . '/api/logs';
        $logs = json_decode(file_get_contents($server_url), true);
        return view('server.logs.index', compact('server', 'logs'));
    }

    public function show($server_id, $log): \Illuminate\View\View
    {
        $server = Server::where(['server_id' => $server_id])->first();
        $server_url = "http://". $server->ip . '/api/logs/' . $log;
        $log = json_decode(file_get_contents($server_url), true);
        return view('server.logs.show', compact('server', 'log'));
    }
}

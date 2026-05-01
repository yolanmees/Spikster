<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ShellController
 *
 * Serves shell scripts for server setup and site operations.
 * Scripts are stored in storage/app/spikster/ (previously cipi/).
 */
class ShellController extends Controller
{
    /**
     * Server setup script (bootstrap a fresh VPS).
     */
    public function setup(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 0)->firstOrFail();

        $script = Storage::get('spikster/setup.sh');
        $script = Str::replaceArray('???', [
            $server->password,
            $server->database,
            $server->server_id,
        ], $script);

        return response($script)->withHeaders(['Content-Type' => 'application/x-sh']);
    }

    /**
     * Site deploy script.
     */
    public function deploy(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        $script = Storage::get('spikster/deploy.sh');
        $script = str_replace('???USER???', $site->username, $script);
        $script = str_replace('???REPO???', $site->repository, $script);
        $script = str_replace('???BRANCH???', $site->branch, $script);
        $script = str_replace('???SCRIPT???', $site->deploy, $script);

        return response($script)->withHeaders(['Content-Type' => 'application/x-sh']);
    }

    /**
     * Root password reset script.
     */
    public function serversrootreset()
    {
        $script = Storage::get('spikster/rootreset.sh');

        return response($script)->withHeaders(['Content-Type' => 'application/x-sh']);
    }
}

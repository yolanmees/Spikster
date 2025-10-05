<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServerRequest;
use App\Jobs\CronSSH;
use App\Jobs\PanelDomainAddSSH;
use App\Jobs\PanelDomainRemoveSSH;
use App\Jobs\PanelDomainSslSSH;
use App\Jobs\PhpCliSSH;
use App\Jobs\RootResetSSH;
use App\Models\Server;
use App\Models\Site;
use App\Models\Stats\Cpu;
use App\Models\Stats\Disk;
use App\Models\Stats\Load;
use App\Models\Stats\Mem;
use App\Models\Userdatabase;
use App\Services\ServerService;
use App\Services\SSHService;
use App\Services\MonitoringService;
use App\Services\Fail2banService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use phpseclib3\Net\SSH2;
use Symfony\Component\Process\Process;

class ServerController extends Controller
{
    public function __construct(
        protected ServerService $serverService,
        protected SSHService $sshService,
        protected MonitoringService $monitoringService,
        protected Fail2banService $fail2banService
    ) {}

    /**
     * List all servers
     *
     * @OA\Get(
     *      path="/api/servers",
     *      summary="List all servers",
     *      tags={"Servers"},
     *      description="List all servers managed by panel.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *
     *          @OA\JsonContent(
     *              type="array",
     *
     *              @OA\Items(
     *
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="name",
     *                    description="Server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="ip",
     *                    description="Server IP",
     *                    type="string",
     *                    example="123.123.123.123",
     *                ),
     *                @OA\Property(
     *                    property="provider",
     *                    description="Server provider",
     *                    type="string",
     *                    example="AWS",
     *                ),
     *                @OA\Property(
     *                    property="location",
     *                    description="Server location",
     *                    type="string",
     *                    example="Frankfurt"
     *                ),
     *                @OA\Property(
     *                    property="php",
     *                    description="Server PHP CLI version",
     *                    type="string",
     *                    example="7.4"
     *                ),
     *                @OA\Property(
     *                    property="default",
     *                    description="Server default status (panel server)",
     *                    type="boolean",
     *                    example="false"
     *                ),
     *                @OA\Property(
     *                    property="status",
     *                    description="Server installation status (0 not installed, 1 installed)",
     *                    type="integer",
     *                    example="1"
     *                ),
     *                @OA\Property(
     *                    property="sites",
     *                    description="The number of sites on this server",
     *                    type="integer",
     *                    example="12"
     *                ),
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
     */
    public function index()
    {
        $servers = $this->serverService->getAllServers();
        $response = [];

        foreach ($servers as $server) {
            $stats = $this->serverService->getServerStats($server);
            $data = [
                'server_id' => $server->server_id,
                'name' => $server->name,
                'ip' => $server->ip,
                'provider' => $server->provider,
                'location' => $server->location,
                'default' => $server->default,
                'status' => $server->status,
                'sites' => $stats['sites_count'],
            ];
            array_push($response, $data);
        }

        return response()->json($response, 200);
    }

    /**
     * Add a new server
     *
     * @OA\Post(
     *      path="/api/servers",
     *      summary="Add a new Server",
     *      tags={"Servers"},
     *      description="Add a new server to manage with panel.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Server creation payload",
     *
     *        @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *             ),
     *             @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Production Server",
     *                  minLength=3
     *             ),
     *             @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="Digital Ocean",
     *             ),
     *             @OA\Property(
     *                  property="location",
     *                  description="Server location",
     *                  type="string",
     *                  example="Amsterdam",
     *             ),
     *             required={"ip","name"}
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful server creation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="location",
     *                  description="Server location",
     *                  type="string",
     *                  example="Frankfurt"
     *              ),
     *              @OA\Property(
     *                  property="setup",
     *                  description="Server setup script",
     *                  type="string",
     *                  example="https://panel.domain.ltd/sh/setup/123456"
     *              ),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=409,
     *          description="Server conflict"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
     */
    public function create(StoreServerRequest $request)
    {
        // Check if IP conflicts with current server
        if ($request->ip == $request->server('SERVER_ADDR')) {
            return response()->json([
                'message' => __('spikster.server_conflict_ip_current_message'),
                'errors' => __('spikster.server_conflict'),
            ], 409);
        }

        // Check if IP already exists
        if (Server::where('ip', $request->ip)->first()) {
            return response()->json([
                'message' => __('spikster.server_conflict_ip_duplicate_message'),
                'errors' => __('spikster.server_conflict'),
            ], 409);
        }

        // Create server using service
        $server = $this->serverService->createServer([
            'ip' => $request->ip,
            'name' => $request->name,
            'provider' => $request->provider,
            'location' => $request->location,
            'password' => Str::random(24),
            'database' => Str::random(24),
            'cron' => ' ',
        ]);

        return response()->json([
            'server_id' => $server->server_id,
            'name' => $server->name,
            'provider' => $server->provider,
            'location' => $server->location,
            'ip' => $server->ip,
            'setup' => URL::to('/sh/setup/'.$server->server_id),
        ]);
    }

    /**
     * Delete a server
     *
     * @OA\Delete(
     *      path="/api/servers/{server_id}",
     *      summary="Delete a Server",
     *      tags={"Servers"},
     *      description="Delete a server from panel.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to delete.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful server deleted",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
     */
    public function destroy(string $server_id)
    {
        $server = $this->serverService->getServerById($server_id);

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message_default'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        if ($server->default) {
            return response()->json([
                'message' => __('spikster.delete_default_server_message'),
                'errors' => __('spikster.bad_request'),
            ], 400);
        }

        try {
            $this->serverService->deleteServer($server);

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => __('spikster.bad_request'),
            ], 400);
        }
    }

    /**
     * Server information
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}",
     *      summary="Server information",
     *      tags={"Servers"},
     *      description="Get server information.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful server information",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="default",
     *                  description="Server default status (panel server)",
     *                  type="boolean",
     *                  example="false"
     *              ),
     *              @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4"
     *              ),
     *              @OA\Property(
     *                  property="github_key",
     *                  description="Server Github deploy key",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="build",
     *                  description="Server build version",
     *                  type="integer",
     *                  example="20210317001"
     *              ),
     *              @OA\Property(
     *                  property="cron",
     *                  description="Server cron",
     *                  type="text",
     *              ),
     *               @OA\Property(
     *                    property="sites",
     *                    description="The number of sites on this server",
     *                    type="integer",
     *                    example="12"
     *                ),
     *          )
     *     ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
     */
    public function show(string $server_id)
    {
        $server = $this->serverService->getServerById($server_id);

        if (! $server || ! $server->isActive()) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $stats = $this->serverService->getServerStats($server);

        return response()->json([
            'sever_id' => $server->server_id,
            'name' => $server->name,
            'ip' => $server->ip,
            'location' => $server->location,
            'provider' => $server->provider,
            'default' => $server->default,
            'php' => $server->php,
            'github_key' => $server->github_key,
            'build' => $server->build,
            'cron' => $server->cron,
            'sites' => $stats['sites_count'],
        ]);
    }

    /**
     * Panel server information
     *
     * @OA\Get(
     *      path="/api/servers/panel",
     *      summary="Panel server information",
     *      tags={"Servers"},
     *      description="Get panel server information.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful server information",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="domain",
     *                  description="Server default domain for panel",
     *                  type="string",
     *                  example="panel.domain.ltd"
     *              ),
     *              @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4"
     *              ),
     *              @OA\Property(
     *                  property="github_key",
     *                  description="Server Github deploy key",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="build",
     *                  description="Server build version",
     *                  type="integer",
     *                  example="20210317001"
     *              ),
     *              @OA\Property(
     *                  property="cron",
     *                  description="Server cron",
     *                  type="text",
     *              ),
     *               @OA\Property(
     *                    property="sites",
     *                    description="The number of sites on this server",
     *                    type="integer",
     *                    example="12"
     *                ),
     *          )
     *     ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     * )
     */
    public function panel()
    {
        $server = Server::where('default', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_native_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $site = Site::where('server_id', $server->id)->where('panel', 1)->first();

        if (! $site) {
            $domain = '';
        } else {
            $domain = $site->domain;
        }

        return response()->json([
            'sever_id' => $server->server_id,
            'name' => $server->name,
            'ip' => $server->ip,
            'location' => $server->location,
            'provider' => $server->provider,
            'domain' => $domain,
            'php' => $server->php,
            'github_key' => $server->github_key,
            'build' => $server->build,
            'cron' => $server->cron,
            'sites' => count($server->sites),
        ]);
    }

    /**
     * Add a domain / subdomain to panel
     *
     * @OA\Patch(
     *      path="/api/servers/panel/domain",
     *      summary="Add a domain / subdomain to panel",
     *      tags={"Servers"},
     *      description="Add a domain / subdomain to panel.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Panel domain payload",
     *
     *        @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                  property="domain",
     *                  description="Panel domain",
     *                  type="string",
     *                  example="panel.domain.ltd",
     *             ),
     *          )
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful panel domain update",
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     * )
     */
    public function paneldomain(Request $request)
    {
        $server = Server::where('default', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_native_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $site = Site::where('server_id', $server->id)->where('panel', true)->first();
        if ($site) {
            $site->delete();
            PanelDomainRemoveSSH::dispatch($server)->delay(Carbon::now()->addSeconds(3));
        }

        if ($request->domain && $request->domain != '') {
            $validator = Validator::make($request->all(), [
                'domain' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => __('spikster.bad_request'),
                    'errors' => $validator->errors()->getMessages(),
                ], 400);
            }
            $newsite = new Site;
            $newsite->server_id = $server->id;
            $newsite->domain = $request->domain;
            $newsite->site_id = sha1(microtime());
            $newsite->username = md5(microtime());
            $newsite->password = 'Secret_123';
            $newsite->database = 'Secret_123';
            $newsite->panel = true;
            $newsite->save();
            PanelDomainAddSSH::dispatch($server)->delay(Carbon::now()->addSeconds(3));
        }

        return response()->json([]);
    }

    /**
     * Require SSL for panel
     *
     * @OA\Post(
     *      path="/api/servers/panel/ssl",
     *      summary="Require SSL for panel",
     *      tags={"Servers"},
     *      description="Require SSL for panel domain / subdomain.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful SSL generation"
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      ),
     * )
     */
    public function panelssl()
    {
        $server = Server::where('default', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_native_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $site = Site::where('server_id', $server->id)->where('panel', true)->first();

        if ($site) {
            PanelDomainSslSSH::dispatch($server, $site)->delay(Carbon::now()->addSeconds(3));
        } else {
            return response()->json([
                'message' => __('spikster.ssl_request_error_message'),
                'errors' => __('spikster.bad_request'),
            ], 400);
        }

        return response()->json([]);
    }

    /**
     * Server edit
     *
     * @OA\Patch(
     *      path="/api/servers/{server_id}",
     *      summary="Server edit",
     *      tags={"Servers"},
     *      description="Edit server information.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to edit.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Server creation payload",
     *
     *        @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *             ),
     *             @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Production Server",
     *                  minLength=3
     *             ),
     *             @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="Digital Ocean",
     *             ),
     *             @OA\Property(
     *                  property="location",
     *                  description="Server location",
     *                  type="string",
     *                  example="Amsterdam",
     *             ),
     *             @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4",
     *             ),
     *             @OA\Property(
     *                  property="cron",
     *                  description="Server crontab",
     *                  type="text",
     *             ),
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful server editing",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="server_id",
     *                  description="Server unique ID",
     *                  type="string",
     *                  example="abc-123-def-456"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Server name",
     *                  type="string",
     *                  example="Staging Server",
     *              ),
     *              @OA\Property(
     *                  property="ip",
     *                  description="Server IP",
     *                  type="string",
     *                  example="123.123.123.123",
     *              ),
     *              @OA\Property(
     *                  property="provider",
     *                  description="Server provider",
     *                  type="string",
     *                  example="AWS",
     *              ),
     *              @OA\Property(
     *                  property="default",
     *                  description="Server default status (panel server)",
     *                  type="boolean",
     *                  example="false"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="Server status",
     *                  type="integer",
     *                  example="1"
     *              ),
     *              @OA\Property(
     *                  property="php",
     *                  description="Server PHP CLI version",
     *                  type="string",
     *                  example="7.4"
     *              ),
     *              @OA\Property(
     *                  property="github_key",
     *                  description="Server Github deploy key",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="build",
     *                  description="Server build version",
     *                  type="integer",
     *                  example="20210317001"
     *              ),
     *              @OA\Property(
     *                  property="cron",
     *                  description="Server cron",
     *                  type="text",
     *              ),
     *          )
     *     ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Server conflict"
     *      ),
     * )
     */
    public function edit(Request $request, string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        if ($request->ip) {
            $validator = Validator::make($request->all(), [
                'ip' => 'required|ip',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => __('spikster.bad_request'),
                    'errors' => $validator->errors()->getMessages(),
                ], 400);
            }
            if (! $server->default && $request->ip == str_replace("\n", '', file_get_contents('https://checkip.amazonaws.com'))) {
                return response()->json([
                    'message' => __('spikster.edit_server_current_ip_error_message'),
                    'errors' => __('spikster.server_conflict'),
                ], 409);
            }
            if (Server::where('ip', $request->ip)->where('server_id', '<>', $server_id)->first()) {
                return response()->json([
                    'message' => __('spikster.server_conflict_ip_duplicate_message'),
                    'errors' => __('spikster.server_conflict'),
                ], 409);
            }
            if ($server->default) {
                $server->ip = str_replace("\n", '', file_get_contents('https://checkip.amazonaws.com'));
            } else {
                $server->ip = $request->ip;
            }
        }

        if ($request->name) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => __('spikster.bad_request'),
                    'errors' => $validator->errors()->getMessages(),
                ], 400);
            }
            $server->name = $request->name;
        }

        if ($request->provider) {
            $server->provider = $request->provider;
        }

        if ($request->location) {
            $server->location = $request->location;
        }

        if ($request->cron) {
            $server->cron = $request->cron;
            $server->save();
            CronSSH::dispatch($server)->delay(Carbon::now()->addSeconds(3));
        }

        if ($request->php) {
            if (! in_array($request->php, config('cipi.phpvers'))) {
                return response()->json([
                    'message' => __('spikster.bad_request'),
                    'errors' => 'Invalid PHP version.',
                ], 400);
            }
            PhpCliSSH::dispatch($server, $request->php)->delay(Carbon::now()->addSeconds(3));
            $server->php = $request->php;
        }

        $server->save();

        return response()->json([
            'sever_id' => $server->server_id,
            'name' => $server->name,
            'ip' => $server->ip,
            'location' => $server->location,
            'provider' => $server->provider,
            'default' => $server->default,
            'status' => $server->status,
            'php' => $server->php,
            'github_key' => $server->github_key,
            'build' => $server->build,
            'cron' => $server->cron,
        ]);
    }

    /**
     * Server ping
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/ping",
     *      summary="Server ping",
     *      tags={"Servers"},
     *      description="Check real time server ping.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to check.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful server ping check",
     *     ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=503,
     *          description="Server unavailable"
     *      ),
     * )
     */
    public function ping(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
                'status' => 'offline',
            ], 404);
        }

        try {
            // Try to ping the server's API logs endpoint (lightweight check)
            $startTime = microtime(true);
            $remote = Http::timeout(5)->get('http://'.$server->ip.'/api/logs');
            $responseTime = round((microtime(true) - $startTime) * 1000); // Convert to milliseconds

            if ($remote->successful()) {
                return response()->json([
                    'message' => 'Server is online',
                    'status' => 'online',
                    'response_time' => $responseTime . 'ms',
                ], 200);
            } else {
                return response()->json([
                    'message' => __('spikster.server_unavailable_message'),
                    'errors' => __('spikster.server_unavailable'),
                    'status' => 'offline',
                ], 503);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.server_unavailable_message'),
                'errors' => $th->getMessage(),
                'status' => 'offline',
            ], 503);
        }
    }

    /**
     * Server healthy
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/healthy",
     *      summary="Server healthy",
     *      tags={"Servers"},
     *      description="Check real time server healthy.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server to check.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful server healthy check",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="cpu",
     *                  description="Current usage of CPU in %",
     *                  type="float",
     *                  example="72.50"
     *              ),
     *              @OA\Property(
     *                  property="ram",
     *                  description="Current usage of RAM in %",
     *                  type="float",
     *                  example="56.34",
     *              ),
     *              @OA\Property(
     *                  property="hdd",
     *                  description="Current usage of HDD in %",
     *                  type="float",
     *                  example="32",
     *              ),
     *          )
     *     ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      ),
     * )
     */
    public function healthy(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        // Get latest metrics from new monitoring system
        $metrics = $this->monitoringService->getLatestMetrics($server);

        if (!$metrics) {
            // No metrics available - return zeros
            return response()->json([
                'cpu' => '0',
                'ram' => '0',
                'hdd' => '0',
            ]);
        }

        return response()->json([
            'cpu' => number_format($metrics['cpu']['percent'], 2),
            'ram' => number_format($metrics['memory']['percent'], 2),
            'hdd' => number_format($metrics['disk']['percent'], 2),
        ]);
    }

    /**
     * Server root password reset
     *
     * @OA\Post(
     *      path="/api/servers/{server_id}/rootreset",
     *      summary="Server root password reset",
     *      tags={"Servers"},
     *      description="Reset server root password (for cipi user).",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful password reset",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="password",
     *                  description="New assigned password for cipi root user",
     *                  type="string",
     *                  example="Secret_123"
     *              ),
     *          )
     *     ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     * )
     */
    public function rootreset(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $last_password = $server->password;
        $new_password = Str::random(24);
        $server->password = $new_password;
        $server->save();

        RootResetSSH::dispatch($server, $new_password, $last_password)->delay(Carbon::now()->addSeconds(1));

        return response()->json([
            'password' => $server->password,
        ]);
    }

    /**
     * Server service restart
     *
     * @OA\Post(
     *      path="/api/servers/{server_id}/servicerestart/{service}",
     *      summary="Server service restart",
     *      tags={"Servers"},
     *      description="Restart a server server (nginx, php, mysql, redis or supervisor).",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *          name="service",
     *          description="The service to restart.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful service restart"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      ),
     * )
     */
    // public function servicerestart(string $server_id, string $service)
    // {
    //     if (!in_array($service, config('cipi.services'))) {
    //         return response()->json([
    //             'message' => __('spikster.invalid_service_error_message'),
    //             'errors' => __('spikster.bad_request')
    //         ], 400);
    //     }

    //     $server = Server::where('server_id', $server_id)->where('status', 1)->first();
    //     if (!$server) {
    //         return response()->json([
    //             'message' => __('spikster.server_not_found_message'),
    //             'errors' => __('spikster.server_not_found')
    //         ], 404);
    //     }

    //     try {
    //         $ssh = new SSH2($server->ip, 22);
    //         if (!$ssh->login('spikster', $server->password)) {
    //             return response()->json([
    //                 'message' => __('spikster.server_error_ssh_error_message').$server->server_id,
    //                 'errors' => __('spikster.server_error')
    //             ], 500);
    //         }

    //         $ssh->setTimeout(360);
    //         switch ($service) {
    //             case 'nginx':
    //                 $ssh->exec('sudo systemctl restart nginx.service');
    //                 break;
    //             case 'php':
    //                 $ssh->exec('sudo service php8.3-fpm restart');
    //                 $ssh->exec('sudo service php8.2-fpm restart');
    //                 $ssh->exec('sudo service php8.1-fpm restart');
    //                 $ssh->exec('sudo service php8.0-fpm restart');
    //                 $ssh->exec('sudo service php7.4-fpm restart');
    //                 $ssh->exec('sudo service php7.3-fpm restart');
    //                 break;
    //             case 'mysql':
    //                 $ssh->exec('sudo service mysql restart');
    //                 break;
    //             case 'redis':
    //                 $ssh->exec('sudo systemctl restart redis.service');
    //                 break;
    //             case 'supervisor':
    //                 $ssh->exec('service supervisor restart');
    //                 break;
    //             default:
    //                 //
    //                 break;
    //         }
    //         $ssh->exec('exit');

    //         return response()->json([]);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'message' => __('spikster.something_error_message'),
    //             'errors' => __('spikster.error')
    //         ], 500);
    //     }
    // }

    /**
     * List all server sites
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/sites",
     *      summary="List all server sites",
     *      tags={"Servers"},
     *      description="List all sites in required server.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *
     *          @OA\JsonContent(
     *              type="array",
     *
     *              @OA\Items(
     *
     *                @OA\Property(
     *                    property="site_id",
     *                    description="Site unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="domain",
     *                    description="Main site domain",
     *                    type="string",
     *                    example="domain.ltd"
     *                ),
     *                @OA\Property(
     *                    property="username",
     *                    description="Site username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="php",
     *                    description="Site PHP version",
     *                    type="string",
     *                    example="7.4"
     *                ),
     *                @OA\Property(
     *                    property="basepath",
     *                    description="Site basepath",
     *                    type="string",
     *                    example="/public"
     *                ),
     *                @OA\Property(
     *                    property="aliases",
     *                    description="The number of aliases of this site",
     *                    type="integer",
     *                    example="8"
     *                ),
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
     */
    public function sites(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $sites = Site::where('panel', false)->where('server_id', $server->id)->get();
        $response = [];

        foreach ($sites as $site) {
            $data = [
                'site_id' => $site->site_id,
                'domain' => $site->domain,
                'username' => $site->username,
                'php' => $site->php,
                'basepath' => $site->basepath,
                'aliases' => count($site->aliases),
            ];
            array_push($response, $data);
        }

        return response()->json($response);
    }

    /**
     * List all server domains
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/domains",
     *      summary="List all server domains",
     *      tags={"Servers"},
     *      description="List all domains hosted in required server.",
     *
     *      @OA\Parameter(
     *          name="Authorization",
     *          description="Use Apikey prefix (e.g. Authorization: Apikey XYZ)",
     *          required=true,
     *          in="header",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="The id of the server.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successfull response (Domain list array)"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found or not installed"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
     */
    public function domains(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $response = [];

        foreach ($server->allsites as $site) {
            array_push($response, $site->domain);
            foreach ($site->aliases as $alias) {
                array_push($response, $alias->domain);
            }
        }

        return response()->json($response);
    }

    public function createdatabase(Request $request)
    {
        $database = new Userdatabase;
        $database->user_id = Auth::user()->id;
        $database->database_name = $request->database_name;

        if ($database->save()) {
            return redirect()->back()->with('success', 'You have successfully Created database!');
        } else {
            return redirect()->back()->with('failed', 'Unable to Create database!');
        }
    }

    public function fail2ban(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        try {
            $ssh = new SSH2($server->ip, 22);
            if (! $ssh->login('spikster', $server->password)) {
                return response()->json([
                    'message' => __('spikster.server_error_ssh_error_message').$server->server_id,
                    'errors' => __('spikster.server_error'),
                ], 500);
            }
            $ssh->setTimeout(360);
            $iptables = $ssh->exec("sqlite3 /var/lib/fail2ban/fail2ban.sqlite3 'select ip,jail from bips'");
            $ssh->exec('exit');
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }

        $iptables = explode("\n", $iptables);
        foreach ($iptables as $i => $iprow) {
            if ($iprow == '') {
                unset($iptables[$i]);
            } else {
                $iptables[$i] = explode('|', $iprow);
            }
        }

        return response()->json([
            $iptables,
        ]);
    }

    public function packages(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        try {
            $ssh = new SSH2($server->ip, 22);
            if (! $ssh->login('spikster', $server->password)) {
                return response()->json([
                    'message' => __('spikster.server_error_ssh_error_message').$server->server_id,
                    'errors' => __('spikster.server_error'),
                ], 500);
            }
            $ssh->setTimeout(360);
            $packages = $ssh->exec('dpkg --get-selections');
            $ssh->exec('exit');
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

        return response()->json([
            $packages,
        ]);
    }

    public function installPackage(string $server_id, Request $request)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        $package = $request->package;
        try {
            $ssh = new SSH2($server->ip, 22);
            if (! $ssh->login('spikster', $server->password)) {
                return response()->json([
                    'message' => __('spikster.server_error_ssh_error_message').$server->server_id,
                    'errors' => __('spikster.server_error'),
                ], 500);
            }
            $ssh->setTimeout(360);
            $packages = $ssh->exec("echo '".$server->password."' | sudo -S apt-get install -y $package");
            $ssh->exec('exit');
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }

        return response()->json([
            $packages,
        ]);
    }

    public function uninstallPackage(string $server_id, Request $request)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        $package = $request->package;
        try {
            $ssh = new SSH2($server->ip, 22);
            if (! $ssh->login('spikster', $server->password)) {
                return response()->json([
                    'message' => __('spikster.server_error_ssh_error_message').$server->server_id,
                    'errors' => __('spikster.server_error'),
                ], 500);
            }
            $ssh->setTimeout(360);
            $packages = $ssh->exec("echo '".$server->password."' | sudo -S apt-get remove -y $package");
            $ssh->exec('exit');
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }

        return response()->json([
            $packages,
        ]);
    }

    public function statsCpu(Server $server)
    {
        $cpu = Cpu::orderBy('created_at', 'desc')->paginate(50);
        $cpu = $cpu->sortBy('created_at');
        if ($cpu->count() > 0) {
            return response()->json([
                'cpu' => $cpu,
            ]);
        } else {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }
    }

    public function statsMem(Server $server)
    {
        $mem = Mem::orderBy('created_at', 'desc')->paginate(50);
        $mem = $mem->sortBy('created_at');
        if ($mem->count() > 0) {
            return response()->json([
                'mem' => $mem,
            ]);
        } else {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }
    }

    public function statsLoad(Server $server)
    {
        $load = Load::orderBy('created_at', 'desc')->paginate(50);
        $load = $load->sortBy('created_at');
        if ($load->count() > 0) {
            return response()->json([
                'load' => $load,
            ]);
        } else {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }
    }

    public function statsDisk(Server $server)
    {
        $disk = Disk::orderBy('created_at', 'desc')->paginate(50);
        $disk = $disk->sortBy('created_at');
        if ($disk->count() > 0) {
            return response()->json([
                'disk' => $disk,
            ]);
        } else {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }
    }

    public function listServices(Request $request)
    {
        $format = $request->get('format', 'json');
        $process = new Process(['sudo', '/var/www/html/bin/spikster', 'list-services', '--format', $format]);

        $process->run();
        if (! $process->isSuccessful()) {
            Log::error('Error executing spikster: '.$process->getErrorOutput());

            return response()->json(['result' => 'error', 'message' => 'Failed to list services', 'details' => $process->getErrorOutput()], 500);
        }

        $output = $process->getOutput();
        $output = preg_replace('/\\\\n/', ' ', $output);

        return response($output)->header('Content-Type', 'application/json');
    }

    public function manageService(Request $request)
    {
        $action = $request->get('action');
        $service = $request->get('service');
        $format = $request->get('format', 'json');

        if (! $action || ! $service) {
            return response()->json(['result' => 'error', 'message' => 'Invalid request parameters'], 400);
        }

        $process = new Process(['bin/spikster', 'manage-services', '--format', $format, $action, $service]);
        $process->run();

        if (! $process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            Log::error('Error executing spikster: '.$errorOutput);

            return response()->json(['result' => 'error', 'message' => 'Failed to manage service'], 500);
        }

        $output = $process->getOutput();

        return response()->json(json_decode($output, true));
    }

    /**
     * Get all Fail2ban jails with statistics
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/fail2ban/jails",
     *      summary="List all Fail2ban jails",
     *      tags={"Fail2ban"},
     *      description="Get all configured Fail2ban jails with statistics",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Server not found"
     *      )
     * )
     */
    public function fail2banJails(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $jails = $this->fail2banService->getJails($server);

            return response()->json([
                'jails' => $jails,
                'total' => count($jails),
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban jails error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to fetch Fail2ban jails',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get status of a specific Fail2ban jail
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/fail2ban/jails/{jail}",
     *      summary="Get jail status",
     *      tags={"Fail2ban"},
     *      description="Get detailed status of a specific jail",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="jail",
     *          description="Jail name",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful request"
     *      )
     * )
     */
    public function fail2banJailStatus(string $server_id, string $jail)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $status = $this->fail2banService->getJailStatus($server, $jail);

            return response()->json([
                'jail' => $jail,
                'status' => $status,
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban jail status error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to fetch jail status',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Ban an IP address
     *
     * @OA\Post(
     *      path="/api/servers/{server_id}/fail2ban/ban",
     *      summary="Ban an IP address",
     *      tags={"Fail2ban"},
     *      description="Manually ban an IP address in a specific jail",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="ip", type="string", example="192.168.1.100"),
     *              @OA\Property(property="jail", type="string", example="sshd")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="IP banned successfully"
     *      )
     * )
     */
    public function fail2banBanIp(Request $request, string $server_id)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'jail' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $ip = $request->input('ip');
            $jail = $request->input('jail', 'sshd');

            $result = $this->fail2banService->banIp($server, $ip, $jail);

            return response()->json([
                'message' => "IP {$ip} banned successfully in jail {$jail}",
                'success' => $result,
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban ban IP error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to ban IP',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Unban an IP address
     *
     * @OA\Post(
     *      path="/api/servers/{server_id}/fail2ban/unban",
     *      summary="Unban an IP address",
     *      tags={"Fail2ban"},
     *      description="Unban an IP address from a specific jail or all jails",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="ip", type="string", example="192.168.1.100"),
     *              @OA\Property(property="jail", type="string", example="sshd")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="IP unbanned successfully"
     *      )
     * )
     */
    public function fail2banUnbanIp(Request $request, string $server_id)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'jail' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $ip = $request->input('ip');
            $jail = $request->input('jail');

            $result = $this->fail2banService->unbanIp($server, $ip, $jail);

            $message = $jail 
                ? "IP {$ip} unbanned successfully from jail {$jail}"
                : "IP {$ip} unbanned successfully from all jails";

            return response()->json([
                'message' => $message,
                'success' => $result,
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban unban IP error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to unban IP',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if IP is banned
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/fail2ban/check/{ip}",
     *      summary="Check if IP is banned",
     *      tags={"Fail2ban"},
     *      description="Check if a specific IP address is currently banned",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="ip",
     *          description="IP address to check",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful request"
     *      )
     * )
     */
    public function fail2banCheckIp(string $server_id, string $ip)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $banned = $this->fail2banService->isIpBanned($server, $ip);

            return response()->json([
                'ip' => $ip,
                'is_banned' => !empty($banned),
                'ban_details' => $banned,
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban check IP error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to check IP status',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Fail2ban statistics
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/fail2ban/stats",
     *      summary="Get Fail2ban statistics",
     *      tags={"Fail2ban"},
     *      description="Get overall Fail2ban statistics for the server",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful request"
     *      )
     * )
     */
    public function fail2banStats(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $stats = $this->fail2banService->getStatistics($server);
            $serviceStatus = $this->fail2banService->getServiceStatus($server);

            return response()->json([
                'statistics' => $stats,
                'service' => $serviceStatus,
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban stats error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to fetch Fail2ban statistics',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Fail2ban logs
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/fail2ban/logs",
     *      summary="Get Fail2ban logs",
     *      tags={"Fail2ban"},
     *      description="Get recent Fail2ban log entries",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="lines",
     *          description="Number of log lines to retrieve",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer", default=100)
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful request"
     *      )
     * )
     */
    public function fail2banLogs(Request $request, string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $lines = $request->input('lines', 100);
            $logs = $this->fail2banService->getLogs($server, $lines);

            return response()->json([
                'logs' => $logs,
                'total' => count($logs),
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban logs error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to fetch Fail2ban logs',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Whitelist an IP address
     *
     * @OA\Post(
     *      path="/api/servers/{server_id}/fail2ban/whitelist",
     *      summary="Whitelist an IP address",
     *      tags={"Fail2ban"},
     *      description="Add an IP address to the Fail2ban whitelist",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="ip", type="string", example="192.168.1.100")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="IP whitelisted successfully"
     *      )
     * )
     */
    public function fail2banWhitelistIp(Request $request, string $server_id)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $ip = $request->input('ip');
            $result = $this->fail2banService->whitelistIp($server, $ip);

            return response()->json([
                'message' => "IP {$ip} whitelisted successfully",
                'success' => $result,
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban whitelist IP error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to whitelist IP',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get whitelisted IPs
     *
     * @OA\Get(
     *      path="/api/servers/{server_id}/fail2ban/whitelist",
     *      summary="Get whitelisted IPs",
     *      tags={"Fail2ban"},
     *      description="Get all whitelisted IP addresses",
     *
     *      @OA\Parameter(
     *          name="server_id",
     *          description="Server unique ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful request"
     *      )
     * )
     */
    public function fail2banGetWhitelist(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (!$server) {
            return response()->json([
                'message' => 'Server not found',
                'errors' => 'Not found',
            ], 404);
        }

        try {
            $whitelist = $this->fail2banService->getWhitelistedIps($server);

            return response()->json([
                'whitelist' => $whitelist,
                'total' => count($whitelist),
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban get whitelist error: ' . $th->getMessage());
            return response()->json([
                'message' => 'Failed to fetch whitelist',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}

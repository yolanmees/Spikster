<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServerRequest;
use App\Models\Server;
use App\Models\ServerMetric;
use App\Models\Site;
use App\Models\Userdatabase;
use App\Services\DaemonService;
use App\Services\ServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ServerController extends Controller
{
    public function __construct(
        protected ServerService $serverService
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
        $this->authorize('viewAny', Server::class);

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
        $this->authorize('create', Server::class);

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

        $this->authorize('delete', $server);

        if ($server->default) {
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

        $this->authorize('view', $server);

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

        $this->authorize('managePanel', $server);

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

        $this->authorize('managePanel', $server);

        $site = Site::where('server_id', $server->id)->where('panel', true)->first();
        if ($site) {
            $site->delete();
            app(DaemonService::class)->send('panel.domain-remove', []);
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
            app(DaemonService::class)->send('panel.domain-add', ['domain' => $server->domain]);
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

        $this->authorize('managePanel', $server);

        $site = Site::where('server_id', $server->id)->where('panel', true)->first();

        if ($site) {
            app(DaemonService::class)->send('panel.domain-ssl', ['domain' => $site->domain]);
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

        $this->authorize('update', $server);

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
            app(DaemonService::class)->writeCron($server->cron);
        }

        if ($request->php) {
            if (! in_array($request->php, config('spikster.phpvers'))) {
                return response()->json([
                    'message' => __('spikster.bad_request'),
                    'errors' => 'Invalid PHP version.',
                ], 400);
            }
            app(DaemonService::class)->send('server.php-cli', ['version' => $request->php]);
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
                    'response_time' => $responseTime.'ms',
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

        $this->authorize('resetPassword', $server);

        $last_password = $server->password;
        $new_password = Str::random(24);
        $server->password = $new_password;
        $server->save();

        app(DaemonService::class)->send('server.root-reset', ['new_pass' => $new_password]);

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
    public function servicerestart(string $server_id, string $service)
    {
        if (! in_array($service, config('spikster.services'))) {
            return response()->json([
                'message' => __('spikster.invalid_service_error_message'),
                'errors' => __('spikster.bad_request'),
            ], 400);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();
        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $this->authorize('manageServices', $server);

        // Service name mapping
        $serviceMap = [
            'nginx' => 'nginx',
            'php' => ['php8.4-fpm', 'php8.3-fpm', 'php8.2-fpm'],
            'mysql' => 'mysql',
            'redis' => 'redis-server',
            'supervisor' => 'supervisor',
        ];

        try {
            $daemon = app(DaemonService::class);
            $services = (array) ($serviceMap[$service] ?? $service);

            foreach ($services as $svc) {
                $daemon->restart($svc);
            }

            return response()->json([]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => __('spikster.something_error_message'),
                'errors' => __('spikster.error'),
            ], 500);
        }
    }

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

        $this->authorize('viewSites', $server);

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

        $this->authorize('viewDomains', $server);

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
        $request->validate([
            'database_name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_]+$/'],
        ]);

        $database = new Userdatabase;
        $database->user_id = Auth::user()->id;
        $database->database_name = $request->database_name;

        if ($database->save()) {
            return redirect()->back()->with('success', 'You have successfully Created database!');
        } else {
            return redirect()->back()->with('failed', 'Unable to Create database!');
        }
    }

    /**
     * Get server health metrics (cpu, ram, hdd percentages).
     */
    public function healthy(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->first();
        if (! $server) {
            return response()->json(['cpu' => 0, 'ram' => 0, 'hdd' => 0, 'status' => 'unknown']);
        }
        try {
            $latest = ServerMetric::where('server_id', $server->id)
                ->orderByDesc('measured_at')
                ->first();
            if ($latest) {
                return response()->json([
                    'cpu' => (int) $latest->cpu,
                    'ram' => (int) $latest->memory,
                    'hdd' => (int) $latest->disk,
                    'status' => 'online',
                ]);
            }

            return response()->json(['cpu' => 0, 'ram' => 0, 'hdd' => 0, 'status' => 'online']);
        } catch (\Throwable $th) {
            return response()->json(['cpu' => 0, 'ram' => 0, 'hdd' => 0, 'status' => 'unknown']);
        }
    }
}

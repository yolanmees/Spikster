<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiQueryHelper;
use App\Models\Server;
use App\Models\Site;
use App\Services\DaemonService;
use App\Services\ServerService;
use App\Services\SiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    use ApiQueryHelper;

    public function __construct(
        protected SiteService $siteService,
        protected ServerService $serverService
    ) {}

    /**
     * List all sites
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Site::class);

        $query = Site::query()->with('server')->where('panel', false);

        $query = $this->applySorting($query, $request, [
            'id', 'domain', 'username', 'php', 'created_at', 'updated_at',
        ]);

        $query = $this->applyFilters($query, $request, [
            'domain' => 'domain.like',
            'php' => 'php',
            'server_id' => 'server_id',
        ]);

        return response()->json(
            $this->paginatedResponse($query, $request, function ($site) {
                $stats = $this->siteService->getSiteStats($site);

                return [
                    'site_id' => $site->site_id,
                    'domain' => $site->domain,
                    'username' => $site->username,
                    'server_id' => $site->server?->server_id,
                    'server_name' => $site->server?->name,
                    'server_ip' => $stats['server']['ip'] ?? null,
                    'php' => $site->php,
                    'basepath' => $site->basepath,
                    'rootpath' => $site->rootpath,
                    'aliases' => $stats['aliases_count'],
                ];
            })
        );
    }

    /**
     * Add a new site
     *
     * @OA\Post(
     *      path="/api/sites",
     *      summary="Add a new site",
     *      tags={"Sites"},
     *      description="Add a new site in panel.",
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
     *        description = "Site creation payload",
     *
     *        @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                  property="server_id",
     *                  description="Site server ID",
     *                  type="string",
     *                  example="abc-123-def-456",
     *             ),
     *             @OA\Property(
     *                  property="domain",
     *                  description="Site main domain",
     *                  type="string",
     *                  example="domain.ltd",
     *             ),
     *             @OA\Property(
     *                    property="php",
     *                    description="Site PHP version",
     *                    type="string",
     *                    example="7.4"
     *               ),
     *             @OA\Property(
     *                  property="basepath",
     *                  description="Site basepath",
     *                  type="string",
     *                  example="public"
     *             ),
     *             required={"server_id","domain"}
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *
     *          @OA\JsonContent(
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
     *                    property="password",
     *                    description="Site password",
     *                    type="string",
     *                    example="Secret_123"
     *                ),
     *                @OA\Property(
     *                    property="database",
     *                    description="Site database",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_username",
     *                    description="Site database username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_password",
     *                    description="Site database password",
     *                    type="string",
     *                    example="Secret_123"
     *                ),
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Related server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="server_name",
     *                    description="Related server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="server_ip",
     *                    description="Related server IP",
     *                    type="string",
     *                    example="123.123.123.123",
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
     *                    example="public"
     *                ),
     *                @OA\Property(
     *                    property="pdf",
     *                    description="Site summary pdf (temp 3 minutes link)",
     *                    type="string",
     *                    example="https://panel.domain.ltd/pdf/123454/1233442"
     *                ),
     *          )
     *      ),
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
     *          description="Site domain conflict"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      )
     * )
     */
    public function create(Request $request)
    {
        $this->authorize('create', Site::class);

        $validator = Validator::make($request->all(), [
            'domain' => 'required',
            'server_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('spikster.bad_request'),
                'errors' => $validator->errors()->getMessages(),
            ], 400);
        }

        if ($request->php) {
            if (! in_array($request->php, config('spikster.phpvers'))) {
                return response()->json([
                    'message' => __('spikster.bad_request'),
                    'errors' => __('spikster.invalid_php_version'),
                ], 400);
            }
            $php = $request->php;
        } else {
            $php = config('spikster.default_php');
        }

        $server = Server::where('server_id', $request->server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json([
                'message' => __('spikster.server_not_found_message'),
                'errors' => __('spikster.server_not_found'),
            ], 404);
        }

        $requestedDomain = strtolower($request->domain);
        $conflict = false;
        foreach ($server->allsites as $checksite) {
            if ($checksite->domain === $requestedDomain) {
                $conflict = true;
                break;
            }
            foreach ($checksite->aliases as $alias) {
                if ($alias->domain === $requestedDomain) {
                    $conflict = true;
                    break 2;
                }
            }
        }
        if ($conflict) {
            return response()->json([
                'message' => __('spikster.site_domain_conflict_message'),
                'errors' => __('spikster.site_domain_conflict'),
            ], 409);
        }

        try {
            $site = $this->siteService->createSite([
                'server_id' => $server->server_id,
                'domain' => $requestedDomain,
                'php' => $php,
                'basepath' => $request->basepath,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => __('spikster.server_connection_issue'),
                'errors' => __('spikster.server_connection_issue'),
            ], 500);
        }

        return response()->json([
            'site_id' => $site->site_id,
            'domain' => $site->domain,
            'username' => $site->username,
            'password' => $site->password,
            'database' => $site->username,
            'database_username' => $site->username,
            'database_password' => $site->database,
            'server_id' => $server->server_id,
            'server_name' => $server->name,
            'server_ip' => $server->ip,
            'php' => $site->php,
            'basepath' => $site->basepath,
        ]);
    }

    /**
     * Edit site information
     *
     * @OA\Patch(
     *      path="/api/sites/{site_id}",
     *      summary="Edit site information",
     *      tags={"Sites"},
     *      description="Edit site information by site_id.",
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
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site to edit.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *     @OA\RequestBody(
     *        required = true,
     *        description = "Site edit payload",
     *
     *        @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                  property="domain",
     *                  description="Site main domain",
     *                  type="string",
     *                  example="domain.ltd",
     *             ),
     *             @OA\Property(
     *                  property="basepath",
     *                  description="Site basepath",
     *                  type="string",
     *                  example="public",
     *             ),
     *             @OA\Property(
     *                  property="php",
     *                  description="PHP FPM version",
     *                  type="string",
     *                  example="8.0",
     *             ),
     *             @OA\Property(
     *                  property="repository",
     *                  description="Github repository",
     *                  type="string",
     *                  example="andreapollastri/cipi",
     *             ),
     *            @OA\Property(
     *                  property="branch",
     *                  description="Git branch",
     *                  type="string",
     *                  example="latest",
     *             ),
     *             @OA\Property(
     *                  property="supervisor",
     *                  description="Supervisor command",
     *                  type="string",
     *                  example="7.4",
     *             ),
     *             @OA\Property(
     *                  property="deploy",
     *                  description="Deploy scripts",
     *                  type="string",
     *             ),
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *
     *          @OA\JsonContent(
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
     *                    property="database",
     *                    description="Site database",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_username",
     *                    description="Site database username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Related server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="server_name",
     *                    description="Related server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="server_ip",
     *                    description="Related server IP",
     *                    type="string",
     *                    example="123.123.123.123",
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
     *                    example="public"
     *                ),
     *                @OA\Property(
     *                       property="repository",
     *                       description="Github repository",
     *                       type="string",
     *                       example="andreapollastri/cipi",
     *                 ),
     *                 @OA\Property(
     *                       property="branch",
     *                       description="Git branch",
     *                       type="string",
     *                       example="latest",
     *                 ),
     *                @OA\Property(
     *                    property="deploy",
     *                    description="Deploy custom configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="deploy_key",
     *                    description="Deploy SSH Key",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="supervisor",
     *                    description="Supervisor configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="aliases",
     *                    description="The count of related aliases",
     *                    type="integer",
     *                    example="8",
     *                ),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
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
     *          description="Site domain conflict"
     *      ),
     * )
     */
    public function edit(Request $request, string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $this->authorize('update', $site);

        if (strtolower($request->domain)) {
            $validator = Validator::make($request->all(), [
                'domain' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => __('spikster.bad_request'),
                    'errors' => $validator->errors()->getMessages(),
                ], 400);
            }

            if ($site->domain != strtolower($request->domain)) {
                $sites = Site::where('server_id', $site->server->id)->get();

                foreach ($sites as $checksite) {
                    if (strtolower($request->domain) == $checksite->domain) {
                        return response()->json([
                            'message' => __('spikster.server_conflict_domain_message'),
                            'errors' => __('spikster.server_conflict'),
                        ], 409);
                    }
                    foreach ($checksite->aliases as $alias) {
                        if (strtolower($request->domain) == $alias->domain) {
                            return response()->json([
                                'message' => __('spikster.server_conflict_alias_message'),
                                'errors' => __('spikster.server_conflict'),
                            ], 409);
                        }
                    }
                }
            }

            $last_domain = $site->domain;
            $site->domain = strtolower($request->domain);
            $site->save();

            app(DaemonService::class)->updateSiteDomain($site->username, $last_domain, $site->domain);
        }

        if ($request->has('basepath')) {
            $basepath = strtolower($request->basepath);
            if ($basepath !== '' && ! str_starts_with($basepath, '/')) {
                return response()->json([
                    'message' => 'Invalid basepath.',
                    'errors' => 'basepath_must_start_with_slash',
                ], 422);
            }
            if (str_contains($basepath, '..')) {
                return response()->json([
                    'message' => 'Invalid basepath.',
                    'errors' => 'basepath_path_traversal',
                ], 422);
            }
            if ($site->basepath != $basepath) {
                $last_basepath = $site->basepath;
                $site->basepath = $basepath;
                $site->save();
                app(DaemonService::class)->updateSiteBasepath($site->username, $site->basepath);
            }
        }

        if ($request->php) {
            $allowedPhpVersions = config('spikster.phpvers', ['8.4', '8.3', '8.2', '8.1', '8.0', '7.4']);
            if (! in_array($request->php, $allowedPhpVersions, true)) {
                return response()->json([
                    'message' => 'Invalid PHP version.',
                    'errors' => 'php_version_not_allowed',
                ], 422);
            }
            if ($site->php != $request->php) {
                $last_php = $site->php;
                $site->php = $request->php;
                $site->save();
                app(DaemonService::class)->updateSitePHP($site->username, $last_php, $site->php);
            }
        }

        $phpSettings = ['php_memory_limit', 'php_upload_max_filesize', 'php_max_execution_time', 'php_max_input_vars', 'php_post_max_size'];
        $phpChanged = false;
        foreach ($phpSettings as $key) {
            if ($request->has($key) && $site->{$key} !== $request->{$key}) {
                if (! is_string($request->{$key})) {
                    return response()->json([
                        'message' => "Invalid value for {$key}.",
                        'errors' => 'invalid_php_setting',
                    ], 422);
                }
                $site->{$key} = $request->{$key};
                $phpChanged = true;
            }
        }
        if ($phpChanged) {
            $site->save();
            app(DaemonService::class)->updateSitePHPSettings($site->fresh());
        }

        if ($request->has('supervisor')) {
            if ($site->supervisor != $request->supervisor) {
                $site->supervisor = $request->supervisor;
                $site->save();
                app(DaemonService::class)->send('site.supervisor', ['username' => $site->username, 'script' => $site->supervisor ?? '']);
            }
        }

        $deploy_patch = false;

        if ($request->deploy) {
            if ($site->deploy != $request->deploy) {
                $site->deploy = $request->deploy;
                $site->save();
                $deploy_patch = true;
            }
        }

        if ($request->repository) {
            if ($site->repository != $request->repository) {
                $site->repository = $request->repository;
                $site->save();
                $deploy_patch = true;
            }
        }

        if ($request->branch) {
            if ($site->branch != $request->branch) {
                $site->branch = $request->branch;
                $site->save();
                $deploy_patch = true;
            }
        }

        if ($request->has('nginx')) {
            if ($site->nginx != $request->nginx) {
                $site->nginx = $request->nginx;
                $site->save();
                app(DaemonService::class)->updateSiteNginxConfig($site->fresh());
            }
        }

        if ($deploy_patch) {
            app(DaemonService::class)->send('site.deploy-script', ['username' => $site->username, 'content' => $site->deploy ?? '']);
        }

        $site->save();

        return response()->json([
            'site_id' => $site->site_id,
            'domain' => $site->domain,
            'username' => $site->username,
            'database' => $site->username,
            'database_username' => $site->username,
            'server_id' => $site->server->server_id,
            'server_name' => $site->server->name,
            'server_ip' => $site->server->ip,
            'php' => $site->php,
            'basepath' => $site->basepath,
            'repository' => $site->repository,
            'branch' => $site->branch,
            'deploy' => $site->deploy,
            'deploy_key' => $site->server->github_key,
            'supervisor' => $site->supervisor,
            'aliases' => count($site->aliases),
        ]);
    }

    /**
     * Show site information
     *
     * @OA\Get(
     *      path="/api/sites/{site_id}",
     *      summary="Show site information",
     *      tags={"Sites"},
     *      description="Get site information by site_id.",
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
     *     @OA\Parameter(
     *          name="site_id",
     *          description="The id of the site to show.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful request",
     *
     *          @OA\JsonContent(
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
     *                    property="database",
     *                    description="Site database",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="database_username",
     *                    description="Site database username",
     *                    type="string",
     *                    example="cp123456"
     *                ),
     *                @OA\Property(
     *                    property="server_id",
     *                    description="Related server unique ID",
     *                    type="string",
     *                    example="abc-123-def-456"
     *                ),
     *                @OA\Property(
     *                    property="server_name",
     *                    description="Related server name",
     *                    type="string",
     *                    example="Staging Server",
     *                ),
     *                @OA\Property(
     *                    property="server_ip",
     *                    description="Related server IP",
     *                    type="string",
     *                    example="123.123.123.123",
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
     *                    example="public"
     *                ),
     *                @OA\Property(
     *                       property="repository",
     *                       description="Github repository",
     *                       type="string",
     *                       example="andreapollastri/cipi",
     *                 ),
     *                 @OA\Property(
     *                       property="branch",
     *                       description="Git branch",
     *                       type="string",
     *                       example="latest",
     *                 ),
     *                @OA\Property(
     *                    property="deploy",
     *                    description="Deploy custom configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="deploy_key",
     *                    description="Deploy SSH Key",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="supervisor",
     *                    description="Supervisor configuration",
     *                    type="string",
     *                ),
     *                @OA\Property(
     *                    property="aliases",
     *                    description="The count of related aliases",
     *                    type="integer",
     *                    example="8",
     *                ),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
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
     *          description="Site domain conflict"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="SSH server connection issue"
     *      )
     * )
     */
    public function show(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $this->authorize('view', $site);

        return response()->json([
            'site_id' => $site->site_id,
            'domain' => $site->domain,
            'username' => $site->username,
            'database' => $site->username,
            'database_username' => $site->username,
            'server_id' => $site->server->server_id,
            'server_name' => $site->server->name,
            'server_ip' => $site->server->ip,
            'php' => $site->php,
            'php_memory_limit' => $site->php_memory_limit ?? '256M',
            'php_upload_max_filesize' => $site->php_upload_max_filesize ?? '256M',
            'php_max_execution_time' => $site->php_max_execution_time ?? '300',
            'php_max_input_vars' => $site->php_max_input_vars ?? '3000',
            'php_post_max_size' => $site->php_post_max_size ?? '256M',
            'node_script' => $site->node_script,
            'node_status' => $site->node_status,
            'basepath' => $site->basepath,
            'repository' => $site->repository,
            'branch' => $site->branch,
            'deploy' => $site->deploy,
            'deploy_key' => $site->server->github_key,
            'nginx' => $site->nginx,
            'supervisor' => $site->supervisor,
            'rootpath' => $site->rootpath,
            'aliases' => count($site->aliases),
        ]);
    }

    /**
     * Delete a Site
     *
     * @OA\Delete(
     *      path="/api/sites/{site_id}",
     *      summary="Delete a Site",
     *      tags={"Sites"},
     *      description="Delete a site from panel.",
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
     *          name="site_id",
     *          description="The id of the site to delete.",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful site deleted",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
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
    public function destroy(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $this->authorize('delete', $site);

        if ($site->panel) {
            return response()->json([
                'message' => __('spikster.bad_request_default_site_delete'),
                'errors' => __('spikster.bad_request'),
            ], 400);
        }

        app(DaemonService::class)->deleteSite([
            'username' => $site->username,
            'php' => $site->php,
            'db_name' => $site->username,
            'db_root' => $site->server->database,
        ]);

        // Delete aliases and the site record from DB
        $site->aliases()->delete();
        $site->delete();

        return response()->json([]);
    }

    /**
     * SSL request for site (and its aliases)
     *
     * @OA\Post(
     *      path="/api/sites/{site_id}/ssl",
     *      summary="SSL request for site (and its aliases)",
     *      tags={"Sites"},
     *      description="Require SSL certs for site and its aliases.",
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
     *          name="site_id",
     *          description="The id of the site to certificate (with its aliases).",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful SSL request",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Site not found"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized access error"
     *      )
     * )
     */
    public function ssl(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $this->authorize('manageSsl', $site);

        app(DaemonService::class)->enableSSL($site->username, $site->domain);

        return response()->json([]);
    }
}

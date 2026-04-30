<?php

namespace App\Http\Controllers;

use App\Models\FtpUser;
use App\Models\Site;
use App\Services\FtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="FTP Management",
 *     description="FTP user management endpoints"
 * )
 */
class FtpController extends Controller
{
    public function __construct(
        protected FtpService $ftpService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/ftp/users",
     *     tags={"FTP Management"},
     *     summary="List all FTP users for a site",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of FTP users",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FtpUser")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request, string $siteId)
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();
        $ftpUsers = $this->ftpService->getUsersForSite($site);

        return response()->json([
            'data' => $ftpUsers,
            'meta' => [
                'total' => $ftpUsers->count(),
                'active' => $ftpUsers->where('is_active', true)->count(),
                'inactive' => $ftpUsers->where('is_active', false)->count(),
                'locked' => $ftpUsers->filter->isLocked()->count(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}",
     *     tags={"FTP Management"},
     *     summary="Get specific FTP user details",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="FTP user details")
     * )
     */
    public function show(string $siteId, string $userId)
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();
        $ftpUser = FtpUser::where('site_id', $siteId)
                         ->findOrFail($userId);

        return response()->json([
            'data' => $ftpUser,
            'statistics' => $ftpUser->getStatistics(),
            'connection_info' => $this->ftpService->getConnectionInfo($ftpUser),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/ftp/users",
     *     tags={"FTP Management"},
     *     summary="Create new FTP user",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="username", type="string", example="user@domain.com"),
     *             @OA\Property(property="password", type="string", example="SecurePass123!"),
     *             @OA\Property(property="quota_mb", type="integer", example=1024),
     *             @OA\Property(property="max_connections", type="integer", example=5),
     *             @OA\Property(property="require_ssl", type="boolean", example=true),
     *             @OA\Property(property="home_directory", type="string", example="/var/www/vhosts/domain.com/httpdocs")
     *         )
     *     ),
     *     @OA\Response(response=201, description="FTP user created successfully")
     * )
     */
    public function store(Request $request, string $siteId)
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|unique:ftp_users|max:255',
            'password' => 'required|string|min:8',
            'home_directory' => 'nullable|string',
            'quota_mb' => 'nullable|integer|min:100|max:100000',
            'max_connections' => 'nullable|integer|min:1|max:20',
            'bandwidth_limit_kbps' => 'nullable|integer|min:128',
            'require_ssl' => 'nullable|boolean',
            'allowed_ip' => 'nullable|ip',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $ftpUser = $this->ftpService->createUser($site, $validator->validated());

        return response()->json([
            'message' => 'FTP user created successfully',
            'data' => $ftpUser,
            'connection_info' => $this->ftpService->getConnectionInfo($ftpUser),
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}",
     *     tags={"FTP Management"},
     *     summary="Update FTP user",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="quota_mb", type="integer"),
     *             @OA\Property(property="max_connections", type="integer"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="FTP user updated")
     * )
     */
    public function update(Request $request, string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'home_directory' => 'sometimes|string',
            'quota_mb' => 'sometimes|integer|min:100|max:100000',
            'max_connections' => 'sometimes|integer|min:1|max:20',
            'bandwidth_limit_kbps' => 'nullable|integer|min:128',
            'is_active' => 'sometimes|boolean',
            'require_ssl' => 'sometimes|boolean',
            'allowed_ip' => 'nullable|ip',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $ftpUser = $this->ftpService->updateUser($ftpUser, $validator->validated());

        return response()->json([
            'message' => 'FTP user updated successfully',
            'data' => $ftpUser,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}",
     *     tags={"FTP Management"},
     *     summary="Delete FTP user",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="FTP user deleted")
     * )
     */
    public function destroy(string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $this->ftpService->deleteUser($ftpUser);

        return response()->json([
            'message' => 'FTP user deleted successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/reset-password",
     *     tags={"FTP Management"},
     *     summary="Reset FTP user password",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", example="NewSecurePass123!")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successful")
     * )
     */
    public function resetPassword(Request $request, string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->ftpService->resetPassword($ftpUser, $validator->validated()['password']);

        return response()->json([
            'message' => 'Password reset successfully',
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/quota",
     *     tags={"FTP Management"},
     *     summary="Update FTP user quota",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quota_mb"},
     *             @OA\Property(property="quota_mb", type="integer", example=2048)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Quota updated")
     * )
     */
    public function updateQuota(Request $request, string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'quota_mb' => 'required|integer|min:100|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->ftpService->updateQuota($ftpUser, $request->quota_mb);

        return response()->json([
            'message' => 'Quota updated successfully',
            'data' => $ftpUser->fresh(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/usage",
     *     tags={"FTP Management"},
     *     summary="Get FTP user usage statistics",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Usage statistics")
     * )
     */
    public function getUsageStats(string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $stats = $this->ftpService->getUsageStats($ftpUser);

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/test-connection",
     *     tags={"FTP Management"},
     *     summary="Test FTP connection",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Connection test result")
     * )
     */
    public function testConnection(Request $request, string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->ftpService->testConnection($ftpUser, $request->password);

        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/connection-info",
     *     tags={"FTP Management"},
     *     summary="Get FTP connection information",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Connection information")
     * )
     */
    public function getConnectionInfo(string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $info = $this->ftpService->getConnectionInfo($ftpUser);

        return response()->json([
            'data' => $info,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/ftp/statistics",
     *     tags={"FTP Management"},
     *     summary="Get FTP statistics for site",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Site FTP statistics")
     * )
     */
    public function getSiteStatistics(string $siteId)
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $stats = $this->ftpService->getSiteStatistics($site);

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/enable",
     *     tags={"FTP Management"},
     *     summary="Enable FTP user",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="FTP user enabled")
     * )
     */
    public function enable(string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $this->ftpService->enableUser($ftpUser);

        return response()->json([
            'message' => 'FTP user enabled successfully',
            'data' => $ftpUser->fresh(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/disable",
     *     tags={"FTP Management"},
     *     summary="Disable FTP user",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="FTP user disabled")
     * )
     */
    public function disable(string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $this->ftpService->disableUser($ftpUser);

        return response()->json([
            'message' => 'FTP user disabled successfully',
            'data' => $ftpUser->fresh(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/ftp/users/{user_id}/unlock",
     *     tags={"FTP Management"},
     *     summary="Unlock FTP user account",
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="FTP user unlocked")
     * )
     */
    public function unlock(string $siteId, string $userId)
    {
        $ftpUser = FtpUser::where('site_id', $siteId)->findOrFail($userId);

        $this->ftpService->unlockUser($ftpUser);

        return response()->json([
            'message' => 'FTP user unlocked successfully',
            'data' => $ftpUser->fresh(),
        ]);
    }
}

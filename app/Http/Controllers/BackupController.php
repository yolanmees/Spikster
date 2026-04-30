<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\BackupStorageLocation;
use App\Models\Site;
use App\Services\BackupService;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Backups",
 *     description="Backup and restore management"
 * )
 */
class BackupController extends Controller
{
    protected BackupService $backupService;
    protected SiteService $siteService;

    public function __construct(BackupService $backupService, SiteService $siteService)
    {
        $this->backupService = $backupService;
        $this->siteService = $siteService;
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/backups",
     *     tags={"Backups"},
     *     summary="List all backups for a site",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="List of backups"),
     *     @OA\Response(response=404, description="Site not found")
     * )
     */
    public function index(string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $backups = Backup::where('site_id', $site->site_id)
            ->with(['backupSchedule', 'server'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($backups);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/backups/full",
     *     tags={"Backups"},
     *     summary="Create a full backup",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="include_database", type="boolean", example=true),
     *             @OA\Property(property="include_files", type="boolean", example=true),
     *             @OA\Property(property="include_email", type="boolean", example=false),
     *             @OA\Property(property="encrypt", type="boolean", example=false),
     *             @OA\Property(property="storage_location", type="string", example="local")
     *         )
     *     ),
     *     @OA\Response(response=202, description="Backup queued"),
     *     @OA\Response(response=404, description="Site not found")
     * )
     */
    public function createFullBackup(Request $request, string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'include_database' => 'boolean',
            'include_files' => 'boolean',
            'include_email' => 'boolean',
            'encrypt' => 'boolean',
            'storage_location' => 'string|in:local,s3,ftp,sftp',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $backup = $this->backupService->createFullBackup($site, $request->all());

        return response()->json([
            'message' => 'Full backup queued successfully',
            'backup' => $backup,
        ], 202);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/backups/incremental",
     *     tags={"Backups"},
     *     summary="Create an incremental backup",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=202, description="Incremental backup queued"),
     *     @OA\Response(response=404, description="Site not found")
     * )
     */
    public function createIncrementalBackup(Request $request, string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $backup = $this->backupService->createIncrementalBackup($site, $request->all());

        return response()->json([
            'message' => 'Incremental backup queued successfully',
            'backup' => $backup,
        ], 202);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/backups/database",
     *     tags={"Backups"},
     *     summary="Create a database-only backup",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=202, description="Database backup queued")
     * )
     */
    public function createDatabaseBackup(Request $request, string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $backup = $this->backupService->createDatabaseBackup($site, $request->all());

        return response()->json([
            'message' => 'Database backup queued successfully',
            'backup' => $backup,
        ], 202);
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/backups/{backup_id}",
     *     tags={"Backups"},
     *     summary="Get backup details",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="backup_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Backup details"),
     *     @OA\Response(response=404, description="Backup not found")
     * )
     */
    public function show(string $siteId, string $backupId): JsonResponse
    {
        $backup = Backup::where('site_id', $siteId)
            ->with(['site', 'server', 'backupSchedule'])
            ->findOrFail($backupId);

        return response()->json($backup);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/backups/{backup_id}/restore",
     *     tags={"Backups"},
     *     summary="Restore a backup",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="backup_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="restore_database", type="boolean", example=true),
     *             @OA\Property(property="restore_files", type="boolean", example=true),
     *             @OA\Property(property="restore_email", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=202, description="Restore queued"),
     *     @OA\Response(response=400, description="Backup cannot be restored"),
     *     @OA\Response(response=404, description="Backup not found")
     * )
     */
    public function restore(Request $request, string $siteId, string $backupId): JsonResponse
    {
        $backup = Backup::where('site_id', $siteId)->findOrFail($backupId);

        $validator = Validator::make($request->all(), [
            'restore_database' => 'boolean',
            'restore_files' => 'boolean',
            'restore_email' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $success = $this->backupService->restoreBackup($backup, $request->all());

        if (!$success) {
            return response()->json([
                'message' => 'Backup cannot be restored',
            ], 400);
        }

        return response()->json([
            'message' => 'Backup restore queued successfully',
        ], 202);
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/backups/{backup_id}/download",
     *     tags={"Backups"},
     *     summary="Download a backup",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="backup_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Backup file"),
     *     @OA\Response(response=404, description="Backup not found")
     * )
     */
    public function download(string $siteId, string $backupId): JsonResponse
    {
        $backup = Backup::where('site_id', $siteId)->findOrFail($backupId);

        $filepath = $this->backupService->downloadBackup($backup);

        if (!$filepath) {
            return response()->json([
                'message' => 'Backup file not available for download',
            ], 404);
        }

        return response()->json([
            'download_url' => route('backups.download.file', ['backup' => $backupId]),
            'filepath' => $filepath,
            'filename' => $backup->filename,
            'size' => $backup->getFormattedSize(),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/sites/{site_id}/backups/{backup_id}",
     *     tags={"Backups"},
     *     summary="Delete a backup",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="backup_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="delete_file",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(response=200, description="Backup deleted"),
     *     @OA\Response(response=404, description="Backup not found")
     * )
     */
    public function destroy(Request $request, string $siteId, string $backupId): JsonResponse
    {
        $backup = Backup::where('site_id', $siteId)->findOrFail($backupId);

        $deleteFile = $request->boolean('delete_file', false);
        $this->backupService->deleteBackup($backup, $deleteFile);

        return response()->json([
            'message' => 'Backup deleted successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/backups/stats",
     *     tags={"Backups"},
     *     summary="Get backup statistics for a site",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Backup statistics")
     * )
     */
    public function stats(string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $stats = $this->backupService->getBackupStats($site);

        return response()->json($stats);
    }

    // ========================================
    // Backup Schedules
    // ========================================

    /**
     * @OA\Get(
     *     path="/api/sites/{site_id}/backup-schedules",
     *     tags={"Backups"},
     *     summary="List backup schedules for a site",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="List of backup schedules")
     * )
     */
    public function listSchedules(string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $schedules = BackupSchedule::where('site_id', $site->site_id)
            ->with('backups')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($schedules);
    }

    /**
     * @OA\Post(
     *     path="/api/sites/{site_id}/backup-schedules",
     *     tags={"Backups"},
     *     summary="Create a backup schedule",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "frequency"},
     *             @OA\Property(property="name", type="string", example="Daily Full Backup"),
     *             @OA\Property(property="type", type="string", enum={"full", "incremental", "database", "files"}),
     *             @OA\Property(property="frequency", type="string", enum={"daily", "weekly", "monthly", "custom"}),
     *             @OA\Property(property="time", type="string", example="02:00"),
     *             @OA\Property(property="day_of_week", type="integer", example=0),
     *             @OA\Property(property="day_of_month", type="integer", example=1),
     *             @OA\Property(property="cron_expression", type="string"),
     *             @OA\Property(property="storage_locations", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="is_encrypted", type="boolean", example=false),
     *             @OA\Property(property="retention_count", type="integer", example=10),
     *             @OA\Property(property="retention_days", type="integer", example=30),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Backup schedule created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createSchedule(Request $request, string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:full,incremental,database,files',
            'frequency' => 'required|in:daily,weekly,monthly,custom',
            'time' => 'nullable|string',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'cron_expression' => 'nullable|string',
            'storage_locations' => 'nullable|array',
            'is_encrypted' => 'boolean',
            'retention_count' => 'nullable|integer|min:1',
            'retention_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedule = $this->backupService->createSchedule($site, $request->all());

        return response()->json([
            'message' => 'Backup schedule created successfully',
            'schedule' => $schedule,
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/sites/{site_id}/backup-schedules/{schedule_id}",
     *     tags={"Backups"},
     *     summary="Update a backup schedule",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="schedule_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Schedule updated"),
     *     @OA\Response(response=404, description="Schedule not found")
     * )
     */
    public function updateSchedule(Request $request, string $siteId, string $scheduleId): JsonResponse
    {
        $schedule = BackupSchedule::where('site_id', $siteId)->findOrFail($scheduleId);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'type' => 'in:full,incremental,database,files',
            'frequency' => 'in:daily,weekly,monthly,custom',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedule = $this->backupService->updateSchedule($schedule, $request->all());

        return response()->json([
            'message' => 'Backup schedule updated successfully',
            'schedule' => $schedule,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/sites/{site_id}/backup-schedules/{schedule_id}",
     *     tags={"Backups"},
     *     summary="Delete a backup schedule",
     *     @OA\Parameter(
     *         name="site_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="schedule_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Schedule deleted"),
     *     @OA\Response(response=404, description="Schedule not found")
     * )
     */
    public function deleteSchedule(Request $request, string $siteId, string $scheduleId): JsonResponse
    {
        $schedule = BackupSchedule::where('site_id', $siteId)->findOrFail($scheduleId);

        $deleteBackups = $request->boolean('delete_backups', false);
        $this->backupService->deleteSchedule($schedule, $deleteBackups);

        return response()->json([
            'message' => 'Backup schedule deleted successfully',
        ]);
    }

    // ========================================
    // Storage Locations
    // ========================================

    /**
     * @OA\Get(
     *     path="/api/backup-storage-locations",
     *     tags={"Backups"},
     *     summary="List all storage locations",
     *     @OA\Response(response=200, description="List of storage locations")
     * )
     */
    public function listStorageLocations(): JsonResponse
    {
        $locations = BackupStorageLocation::active()->get();

        return response()->json($locations);
    }

    /**
     * @OA\Post(
     *     path="/api/backup-storage-locations",
     *     tags={"Backups"},
     *     summary="Create a storage location",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "config"},
     *             @OA\Property(property="name", type="string", example="AWS S3"),
     *             @OA\Property(property="type", type="string", enum={"local", "s3", "ftp", "sftp"}),
     *             @OA\Property(property="config", type="object"),
     *             @OA\Property(property="is_default", type="boolean", example=false),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Storage location created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createStorageLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:local,s3,ftp,sftp',
            'config' => 'required|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = $this->backupService->createStorageLocation($request->all());

        return response()->json([
            'message' => 'Storage location created successfully',
            'location' => $location,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/backup-storage-locations/{location_id}/test",
     *     tags={"Backups"},
     *     summary="Test storage location connection",
     *     @OA\Parameter(
     *         name="location_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Connection test result")
     * )
     */
    public function testStorageConnection(string $locationId): JsonResponse
    {
        $location = BackupStorageLocation::findOrFail($locationId);

        $success = $this->backupService->testStorageConnection($location);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Connection successful' : 'Connection failed',
            'test_status' => $location->test_status,
            'last_test_at' => $location->last_test_at,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/backup-storage-locations/{location_id}",
     *     tags={"Backups"},
     *     summary="Delete a storage location",
     *     @OA\Parameter(
     *         name="location_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Storage location deleted"),
     *     @OA\Response(response=400, description="Cannot delete default location")
     * )
     */
    public function deleteStorageLocation(string $locationId): JsonResponse
    {
        $location = BackupStorageLocation::findOrFail($locationId);

        $success = $this->backupService->deleteStorageLocation($location);

        if (!$success) {
            return response()->json([
                'message' => 'Cannot delete default storage location',
            ], 400);
        }

        return response()->json([
            'message' => 'Storage location deleted successfully',
        ]);
    }
}

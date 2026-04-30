<?php

use App\Http\Controllers\Api\LogManagerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Servers
Route::get('/servers', [ServerController::class, 'index']);
Route::post('/servers', [ServerController::class, 'create']);
Route::get('/servers/panel', [ServerController::class, 'panel']);
Route::patch('/servers/panel/domain', [ServerController::class, 'paneldomain']);
Route::post('/servers/panel/ssl', [ServerController::class, 'panelssl']);
Route::delete('/servers/{server_id}', [ServerController::class, 'destroy']);
Route::get('/servers/{server_id}', [ServerController::class, 'show']);
Route::patch('/servers/{server_id}', [ServerController::class, 'edit']);
Route::get('/servers/{server_id}/ping', [ServerController::class, 'ping']);
Route::get('/servers/{server_id}/ping', [ServerController::class, 'ping']);
Route::get('/servers/{server_id}/stats/cpu', [ServerController::class, 'statsCpu']);
Route::get('/servers/{server_id}/stats/mem', [ServerController::class, 'statsMem']);
Route::get('/servers/{server_id}/stats/load', [ServerController::class, 'statsLoad']);
Route::get('/servers/{server_id}/stats/disk', [ServerController::class, 'statsDisk']);
Route::get('/servers/{server_id}/metrics', [\App\Http\Controllers\ServerMetricsController::class, 'getChartData']);
Route::post('/servers/{server_id}/rootreset', [ServerController::class, 'rootreset']);
Route::post("/servers/{server_id}/servicerestart/{service}", [ServerController::class, "servicerestart"]);
Route::get('/servers/{server_id}/sites', [ServerController::class, 'sites']);
Route::get('/servers/{server_id}/domains', [ServerController::class, 'domains']);
Route::get('/servers/{server_id}/fail2ban', [ServerController::class, 'fail2ban']);
Route::get('/servers/{server_id}/packages', [ServerController::class, 'packages']);
Route::post('/servers/{server_id}/packages/install', [ServerController::class, 'installPackage']);
Route::post('/servers/{server_id}/packages/uninstall', [ServerController::class, 'uninstallPackage']);
Route::get('/servers/{server_id}/services', [ServerController::class, 'listServices']);
Route::post('/servers/{server_id}/services/manage', [ServerController::class, 'manageService']);

// Fail2ban endpoints
Route::get('/servers/{server_id}/fail2ban/jails', [ServerController::class, 'fail2banJails']);
Route::get('/servers/{server_id}/fail2ban/jails/{jail}', [ServerController::class, 'fail2banJailStatus']);
Route::post('/servers/{server_id}/fail2ban/ban', [ServerController::class, 'fail2banBanIp']);
Route::post('/servers/{server_id}/fail2ban/unban', [ServerController::class, 'fail2banUnbanIp']);
Route::get('/servers/{server_id}/fail2ban/check/{ip}', [ServerController::class, 'fail2banCheckIp']);
Route::get('/servers/{server_id}/fail2ban/stats', [ServerController::class, 'fail2banStats']);
Route::get('/servers/{server_id}/fail2ban/logs', [ServerController::class, 'fail2banLogs']);
Route::post('/servers/{server_id}/fail2ban/whitelist', [ServerController::class, 'fail2banWhitelistIp']);
Route::get('/servers/{server_id}/fail2ban/whitelist', [ServerController::class, 'fail2banGetWhitelist']);
Route::post('/servers/{server_id}/fail2ban/deploy', [ServerController::class, 'fail2banDeploy']);

// Cron Job Execution endpoints
Route::post('/cron-executions', [\App\Http\Controllers\CronExecutionController::class, 'store']);
Route::patch('/cron-executions/{execution}', [\App\Http\Controllers\CronExecutionController::class, 'update']);
Route::get('/cron-jobs/{cronJob}/executions', [\App\Http\Controllers\CronExecutionController::class, 'index']);
Route::get('/cron-executions/{execution}', [\App\Http\Controllers\CronExecutionController::class, 'show']);
Route::delete('/cron-executions/{execution}', [\App\Http\Controllers\CronExecutionController::class, 'destroy']);

// Sites
Route::get('/sites', [SiteController::class, 'index']);
Route::post('/sites', [SiteController::class, 'create']);
Route::patch('/sites/{site_id}', [SiteController::class, 'edit']);
Route::delete('/sites/{site_id}', [SiteController::class, 'destroy']);
Route::get('/sites/{site_id}', [SiteController::class, 'show']);
Route::post('/sites/{site_id}/ssl', [SiteController::class, 'ssl']);
Route::post('/sites/{site_id}/reset/ssh', [SiteController::class, 'resetssh']);
Route::post('/sites/{site_id}/reset/db', [SiteController::class, 'resetdb']);
Route::get('/sites/{site_id}/aliases', [SiteController::class, 'aliases']);
Route::post('/sites/{site_id}/aliases', [SiteController::class, 'createalias']);
Route::delete('/sites/{site_id}/aliases/{alias_id}', [SiteController::class, 'destroyalias']);

// Email Management
Route::get('/sites/{site_id}/email/accounts', [EmailController::class, 'indexAccounts']);
Route::post('/sites/{site_id}/email/accounts', [EmailController::class, 'createAccount']);
Route::patch('/sites/{site_id}/email/accounts/{account_id}', [EmailController::class, 'updateAccount']);
Route::delete('/sites/{site_id}/email/accounts/{account_id}', [EmailController::class, 'deleteAccount']);
Route::get('/sites/{site_id}/email/accounts/{account_id}/quota', [EmailController::class, 'getQuota']);
Route::get('/sites/{site_id}/email/accounts/{account_id}/aliases', [EmailController::class, 'indexAliases']);
Route::post('/sites/{site_id}/email/accounts/{account_id}/aliases', [EmailController::class, 'createAlias']);
Route::post('/sites/{site_id}/email/accounts/{account_id}/autoresponder', [EmailController::class, 'setAutoresponder']);

Route::get('/sites/{site_id}/email/forwarders', [EmailController::class, 'indexForwarders']);
Route::post('/sites/{site_id}/email/forwarders', [EmailController::class, 'createForwarder']);
Route::delete('/sites/{site_id}/email/forwarders/{forwarder_id}', [EmailController::class, 'deleteForwarder']);

Route::delete('/sites/{site_id}/email/aliases/{alias_id}', [EmailController::class, 'deleteAlias']);

Route::post('/sites/{site_id}/email/dkim', [EmailController::class, 'setupDKIM']);
Route::post('/sites/{site_id}/email/spf', [EmailController::class, 'generateSPF']);
Route::post('/sites/{site_id}/email/dmarc', [EmailController::class, 'generateDMARC']);

Route::get('/sites/{site_id}/email/statistics', [EmailController::class, 'getStatistics']);

Route::post('/sites/{site_id}/email/webmail/install', [EmailController::class, 'installWebmail']);
Route::post('/sites/{site_id}/email/accounts/{account_id}/webmail', [EmailController::class, 'getWebmailUrl']);

// Get API Key From API login
Route::post('/login', [AuthController::class, 'appLogin'])->middleware('throttle:10,3');

Route::middleware('api')->group(function () {
    // phpmyadmin route
    Route::get('/pma', function () {
        return redirect()->to('mysecureadmin/index.php');
    });
    // database
    Route::get('/data', [DatabaseController::class, 'viewdatabase'])->name('data');
    Route::post('/createdatab', [DatabaseController::class, 'createdatabase'])->name('createdatab');
    Route::post('/createuser', [DatabaseController::class, 'createuser'])->name('createuser');
    Route::post('/linkdatabuser', [DatabaseController::class, 'linkdatabaseuser'])->name('linkdatabuser');
});

Route::get('files/{folder_name?}', [FileManagerController::class, 'index'])->where('folder_name', '(.*)')->name('files.index');
Route::post('files/view', [FileManagerController::class, 'show'])->name('files.show');
Route::post('files/edit', [FileManagerController::class, 'edit'])->name('files.edit');
Route::post('files/store', [FileManagerController::class, 'store'])->name('files.store');
Route::post('files/download', [FileManagerController::class, 'download'])->name('files.download');
Route::post('files/create-directory', [FileManagerController::class, 'createDirectory'])->name('files.create.directory');
Route::post('files/create-file', [FileManagerController::class, 'createFile'])->name('files.create.file');
Route::post('files/rename-file', [FileManagerController::class, 'renameFile'])->name('files.rename.file');
Route::post('files/copy-file', [FileManagerController::class, 'copy'])->name('files.copy');
Route::post('files/move-file', [FileManagerController::class, 'move'])->name('files.move');
Route::post('files/delete', [FileManagerController::class, 'destroy'])->name('files.delete');

Route::get('download_file_object/{id}', [FileManagerController::class, 'downloadObject']);
Route::get('show-media-file/{id}', [FileManagerController::class, 'showMediaFile']);

Route::get('logs', [LogManagerController::class, 'index'])->name('api.logs');
Route::get('logs/{log}', [LogManagerController::class, 'show'])->name('api.logs.show');
Route::get('logs/{log}/download', [LogManagerController::class, 'download'])->name('api.logs.download');
Route::delete('logs/{log}', [LogManagerController::class, 'delete'])->name('api.logs.delete');

// Backups - Site-specific backup routes
Route::prefix('sites/{site_id}')->group(function () {
    // Backups
    Route::get('/backups', [BackupController::class, 'index']);
    Route::post('/backups/full', [BackupController::class, 'createFullBackup']);
    Route::post('/backups/incremental', [BackupController::class, 'createIncrementalBackup']);
    Route::post('/backups/database', [BackupController::class, 'createDatabaseBackup']);
    Route::get('/backups/stats', [BackupController::class, 'stats']);
    Route::get('/backups/{backup_id}', [BackupController::class, 'show']);
    Route::post('/backups/{backup_id}/restore', [BackupController::class, 'restore']);
    Route::get('/backups/{backup_id}/download', [BackupController::class, 'download']);
    Route::delete('/backups/{backup_id}', [BackupController::class, 'destroy']);

    // Backup Schedules
    Route::get('/backup-schedules', [BackupController::class, 'listSchedules']);
    Route::post('/backup-schedules', [BackupController::class, 'createSchedule']);
    Route::put('/backup-schedules/{schedule_id}', [BackupController::class, 'updateSchedule']);
    Route::delete('/backup-schedules/{schedule_id}', [BackupController::class, 'deleteSchedule']);
});

// Backup Storage Locations (global, not site-specific)
Route::get('/backup-storage-locations', [BackupController::class, 'listStorageLocations']);
Route::post('/backup-storage-locations', [BackupController::class, 'createStorageLocation']);
Route::post('/backup-storage-locations/{location_id}/test', [BackupController::class, 'testStorageConnection']);
Route::delete('/backup-storage-locations/{location_id}', [BackupController::class, 'deleteStorageLocation']);

// FTP Management - Site-specific FTP routes
Route::prefix('sites/{site_id}')->group(function () {
    // FTP Users
    Route::get('/ftp/users', [\App\Http\Controllers\FtpController::class, 'index']);
    Route::post('/ftp/users', [\App\Http\Controllers\FtpController::class, 'store']);
    Route::get('/ftp/users/{user_id}', [\App\Http\Controllers\FtpController::class, 'show']);
    Route::put('/ftp/users/{user_id}', [\App\Http\Controllers\FtpController::class, 'update']);
    Route::delete('/ftp/users/{user_id}', [\App\Http\Controllers\FtpController::class, 'destroy']);

    // FTP User Actions
    Route::post('/ftp/users/{user_id}/reset-password', [\App\Http\Controllers\FtpController::class, 'resetPassword']);
    Route::put('/ftp/users/{user_id}/quota', [\App\Http\Controllers\FtpController::class, 'updateQuota']);
    Route::get('/ftp/users/{user_id}/usage', [\App\Http\Controllers\FtpController::class, 'getUsageStats']);
    Route::post('/ftp/users/{user_id}/test-connection', [\App\Http\Controllers\FtpController::class, 'testConnection']);
    Route::get('/ftp/users/{user_id}/connection-info', [\App\Http\Controllers\FtpController::class, 'getConnectionInfo']);
    Route::post('/ftp/users/{user_id}/enable', [\App\Http\Controllers\FtpController::class, 'enable']);
    Route::post('/ftp/users/{user_id}/disable', [\App\Http\Controllers\FtpController::class, 'disable']);
    Route::post('/ftp/users/{user_id}/unlock', [\App\Http\Controllers\FtpController::class, 'unlock']);

    // FTP Statistics
    Route::get('/ftp/statistics', [\App\Http\Controllers\FtpController::class, 'getSiteStatistics']);
});

// Two-Factor Authentication (2FA)
Route::middleware(['auth:sanctum'])->prefix('user/2fa')->group(function () {
    Route::get('/status', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'status']);
    Route::post('/generate-secret', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'generateSecret']);
    Route::post('/enable', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'enable']);
    Route::post('/disable', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'disable']);
    Route::post('/verify', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'verify']);
    Route::post('/backup-codes/regenerate', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'regenerateBackupCodes']);
    Route::get('/trusted-devices', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'getTrustedDevices']);
    Route::delete('/trusted-devices/{deviceId}', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'removeTrustedDevice']);
    Route::get('/audit-logs', [\App\Http\Controllers\API\TwoFactorAuthController::class, 'getAuditLogs']);
});

// Module Management API Routes
Route::prefix('modules')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ModuleController::class, 'index']);
    Route::get('/discover', [\App\Http\Controllers\Api\ModuleController::class, 'discover']);
    Route::post('/install', [\App\Http\Controllers\Api\ModuleController::class, 'install']);
    Route::get('/{module}', [\App\Http\Controllers\Api\ModuleController::class, 'show']);
    Route::post('/{module}/enable', [\App\Http\Controllers\Api\ModuleController::class, 'enable']);
    Route::post('/{module}/disable', [\App\Http\Controllers\Api\ModuleController::class, 'disable']);
    Route::get('/{module}/health', [\App\Http\Controllers\Api\ModuleController::class, 'checkHealth']);
    Route::get('/{module}/dependencies', [\App\Http\Controllers\Api\ModuleController::class, 'checkDependencies']);
});

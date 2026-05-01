<?php

use App\Http\Controllers\Api\DeployController;
use App\Http\Controllers\Api\LogManagerController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\TwoFactorAuthController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CronExecutionController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FtpController;
use App\Http\Controllers\Server\Fail2banController;
use App\Http\Controllers\Server\MonitoringController;
use App\Http\Controllers\Server\PackagesController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerMetricsController;
use App\Http\Controllers\Site\AliasController;
use App\Http\Controllers\Site\CredentialController;
use App\Http\Controllers\Site\SshKeyController;
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

// All protected routes
Route::middleware(['api.unified-auth'])->group(function () {

    // Servers
    Route::get('/servers', [ServerController::class, 'index']);
    Route::post('/servers', [ServerController::class, 'create'])->middleware('idempotency');
    Route::get('/servers/panel', [ServerController::class, 'panel']);
    Route::patch('/servers/panel/domain', [ServerController::class, 'paneldomain']);
    Route::post('/servers/panel/ssl', [ServerController::class, 'panelssl']);
    Route::delete('/servers/{server_id}', [ServerController::class, 'destroy']);
    Route::get('/servers/{server_id}', [ServerController::class, 'show']);
    Route::patch('/servers/{server_id}', [ServerController::class, 'edit']);
    Route::get('/servers/{server_id}/ping', [ServerController::class, 'ping']);
    Route::get('/servers/{server_id}/healthy', [ServerController::class, 'healthy']);
    Route::get('/servers/{server_id}/stats/cpu', [MonitoringController::class, 'statsCpu']);
    Route::get('/servers/{server_id}/stats/mem', [MonitoringController::class, 'statsMem']);
    Route::get('/servers/{server_id}/stats/load', [MonitoringController::class, 'statsLoad']);
    Route::get('/servers/{server_id}/stats/disk', [MonitoringController::class, 'statsDisk']);
    Route::get('/servers/{server_id}/metrics', [ServerMetricsController::class, 'getChartData']);
    Route::post('/servers/{server_id}/rootreset', [ServerController::class, 'rootreset']);
    Route::post('/servers/{server_id}/servicerestart/{service}', [ServerController::class, 'servicerestart']);
    Route::get('/servers/{server_id}/sites', [ServerController::class, 'sites']);
    Route::get('/servers/{server_id}/domains', [ServerController::class, 'domains']);
    Route::get('/servers/{server_id}/fail2ban', [ServerController::class, 'fail2ban']);
    Route::get('/servers/{server_id}/packages', [PackagesController::class, 'index']);
    Route::post('/servers/{server_id}/packages/install', [PackagesController::class, 'install']);
    Route::post('/servers/{server_id}/packages/uninstall', [PackagesController::class, 'uninstall']);
    Route::get('/servers/{server_id}/services', [MonitoringController::class, 'listServices']);
    Route::post('/servers/{server_id}/services/manage', [MonitoringController::class, 'manageService']);

    // Fail2ban endpoints
    Route::get('/servers/{server_id}/fail2ban/jails', [Fail2banController::class, 'jails']);
    Route::get('/servers/{server_id}/fail2ban/jails/{jail}', [Fail2banController::class, 'jailStatus']);
    Route::post('/servers/{server_id}/fail2ban/ban', [Fail2banController::class, 'banIp']);
    Route::post('/servers/{server_id}/fail2ban/unban', [Fail2banController::class, 'unbanIp']);
    Route::get('/servers/{server_id}/fail2ban/check/{ip}', [Fail2banController::class, 'checkIp']);
    Route::get('/servers/{server_id}/fail2ban/stats', [Fail2banController::class, 'stats']);
    Route::get('/servers/{server_id}/fail2ban/logs', [Fail2banController::class, 'logs']);
    Route::post('/servers/{server_id}/fail2ban/whitelist', [Fail2banController::class, 'whitelistIp']);
    Route::get('/servers/{server_id}/fail2ban/whitelist', [Fail2banController::class, 'getWhitelist']);
    // fail2banDeploy removed - relied on deleted SSHService

    // Cron Job Execution endpoints
    Route::post('/cron-executions', [CronExecutionController::class, 'store']);
    Route::patch('/cron-executions/{execution}', [CronExecutionController::class, 'update']);
    Route::get('/cron-jobs/{cronJob}/executions', [CronExecutionController::class, 'index']);
    Route::get('/cron-executions/{execution}', [CronExecutionController::class, 'show']);
    Route::delete('/cron-executions/{execution}', [CronExecutionController::class, 'destroy']);

    // Sites
    Route::get('/sites', [SiteController::class, 'index']);
    Route::post('/sites', [SiteController::class, 'create'])->middleware('idempotency');
    Route::patch('/sites/{site_id}', [SiteController::class, 'edit']);
    Route::delete('/sites/{site_id}', [SiteController::class, 'destroy']);
    Route::get('/sites/{site_id}', [SiteController::class, 'show']);
    Route::post('/sites/{site_id}/ssl', [SiteController::class, 'ssl']);
    Route::post('/sites/{site_id}/reset/ssh', [CredentialController::class, 'resetSsh']);
    Route::post('/sites/{site_id}/reset/db', [CredentialController::class, 'resetDb']);

    // SSH Key Management
    Route::get('/sites/{site_id}/ssh-keys', [SshKeyController::class, 'index']);
    Route::post('/sites/{site_id}/ssh-keys', [SshKeyController::class, 'store']);
    Route::delete('/sites/{site_id}/ssh-keys/{key_id}', [SshKeyController::class, 'destroy']);

    Route::get('/sites/{site_id}/aliases', [AliasController::class, 'index']);
    Route::post('/sites/{site_id}/aliases', [AliasController::class, 'store']);
    Route::delete('/sites/{site_id}/aliases/{alias_id}', [AliasController::class, 'destroy']);

    // Deployments
    Route::post('/sites/{site_id}/deploy', [DeployController::class, 'deploy'])->middleware('idempotency');
    Route::get('/sites/{site_id}/deployments', [DeployController::class, 'history']);
    Route::post('/sites/{site_id}/deployments/{deployment}/rollback', [DeployController::class, 'rollback'])->middleware('idempotency');
    Route::get('/sites/{site_id}/git-history', [DeployController::class, 'gitHistory']);

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

}); // end auth:sanctum

// Get API Key From API login
Route::post('/login', [AuthController::class, 'appLogin'])->middleware('throttle:10,3');

Route::middleware(['api.unified-auth'])->group(function () {
    // database
    Route::get('/data', [DatabaseController::class, 'index'])->name('data');
    Route::post('/createdatab', [DatabaseController::class, 'createDatabase'])->name('createdatab');
    Route::post('/createuser', [DatabaseController::class, 'createUser'])->name('createUser');
    Route::post('/linkdatabuser', [DatabaseController::class, 'linkDatabaseUser'])->name('linkdatabuser');
});

// File manager routes are defined in web.php (behind auth:sanctum)

Route::middleware(['api.unified-auth'])->group(function () {
    Route::get('logs', [LogManagerController::class, 'index'])->name('api.logs');
    Route::get('logs/{log}', [LogManagerController::class, 'show'])->name('api.logs.show');
    Route::get('logs/{log}/download', [LogManagerController::class, 'download'])->name('api.logs.download');
    Route::delete('logs/{log}', [LogManagerController::class, 'delete'])->name('api.logs.delete');
});

// Backups + storage — all behind auth
Route::middleware(['api.unified-auth'])->group(function () {
    Route::prefix('sites/{site_id}')->group(function () {
        Route::get('/backups', [BackupController::class, 'index']);
        Route::post('/backups/full', [BackupController::class, 'createFullBackup'])->middleware('idempotency');
        Route::post('/backups/incremental', [BackupController::class, 'createIncrementalBackup'])->middleware('idempotency');
        Route::post('/backups/database', [BackupController::class, 'createDatabaseBackup'])->middleware('idempotency');
        Route::get('/backups/stats', [BackupController::class, 'stats']);
        Route::get('/backups/{backup_id}', [BackupController::class, 'show']);
        Route::post('/backups/{backup_id}/restore', [BackupController::class, 'restore'])->middleware('idempotency');
        Route::get('/backups/{backup_id}/download', [BackupController::class, 'download']);
        Route::delete('/backups/{backup_id}', [BackupController::class, 'destroy']);

        Route::get('/backup-schedules', [BackupController::class, 'listSchedules']);
        Route::post('/backup-schedules', [BackupController::class, 'createSchedule']);
        Route::put('/backup-schedules/{schedule_id}', [BackupController::class, 'updateSchedule']);
        Route::delete('/backup-schedules/{schedule_id}', [BackupController::class, 'deleteSchedule']);
    });

    Route::get('/backup-storage-locations', [BackupController::class, 'listStorageLocations']);
    Route::post('/backup-storage-locations', [BackupController::class, 'createStorageLocation']);
    Route::post('/backup-storage-locations/{location_id}/test', [BackupController::class, 'testStorageConnection']);
    Route::delete('/backup-storage-locations/{location_id}', [BackupController::class, 'deleteStorageLocation']);
});

// FTP Management - Site-specific FTP routes
Route::middleware(['api.unified-auth'])->prefix('sites/{site_id}')->group(function () {
    // FTP Users
    Route::get('/ftp/users', [FtpController::class, 'index']);
    Route::post('/ftp/users', [FtpController::class, 'store']);
    Route::get('/ftp/users/{user_id}', [FtpController::class, 'show']);
    Route::put('/ftp/users/{user_id}', [FtpController::class, 'update']);
    Route::delete('/ftp/users/{user_id}', [FtpController::class, 'destroy']);

    // FTP User Actions
    Route::post('/ftp/users/{user_id}/reset-password', [FtpController::class, 'resetPassword']);
    Route::put('/ftp/users/{user_id}/quota', [FtpController::class, 'updateQuota']);
    Route::get('/ftp/users/{user_id}/usage', [FtpController::class, 'getUsageStats']);
    Route::post('/ftp/users/{user_id}/test-connection', [FtpController::class, 'testConnection']);
    Route::get('/ftp/users/{user_id}/connection-info', [FtpController::class, 'getConnectionInfo']);
    Route::post('/ftp/users/{user_id}/enable', [FtpController::class, 'enable']);
    Route::post('/ftp/users/{user_id}/disable', [FtpController::class, 'disable']);
    Route::post('/ftp/users/{user_id}/unlock', [FtpController::class, 'unlock']);

    // FTP Statistics
    Route::get('/ftp/statistics', [FtpController::class, 'getSiteStatistics']);
});

// Two-Factor Authentication (2FA)
Route::middleware(['api.unified-auth'])->prefix('user/2fa')->group(function () {
    Route::get('/status', [TwoFactorAuthController::class, 'status']);
    Route::post('/generate-secret', [TwoFactorAuthController::class, 'generateSecret'])->middleware('throttle:sensitive');
    Route::post('/enable', [TwoFactorAuthController::class, 'enable'])->middleware('throttle:sensitive');
    Route::post('/disable', [TwoFactorAuthController::class, 'disable'])->middleware('throttle:sensitive');
    Route::post('/verify', [TwoFactorAuthController::class, 'verify'])->middleware('throttle:sensitive');
    Route::post('/backup-codes/regenerate', [TwoFactorAuthController::class, 'regenerateBackupCodes'])->middleware('throttle:sensitive');
    Route::get('/trusted-devices', [TwoFactorAuthController::class, 'getTrustedDevices']);
    Route::delete('/trusted-devices/{deviceId}', [TwoFactorAuthController::class, 'removeTrustedDevice'])->middleware('throttle:sensitive');
    Route::get('/audit-logs', [TwoFactorAuthController::class, 'getAuditLogs']);
});

// Webhook Management
Route::middleware(['api.unified-auth'])->prefix('webhooks')->group(function () {
    Route::get('/', [WebhookController::class, 'index']);
    Route::post('/', [WebhookController::class, 'store']);
    Route::get('/events', [WebhookController::class, 'events']);
    Route::get('/{webhook}', [WebhookController::class, 'show']);
    Route::patch('/{webhook}', [WebhookController::class, 'update']);
    Route::delete('/{webhook}', [WebhookController::class, 'destroy']);
    Route::post('/{webhook}/test', [WebhookController::class, 'test']);
});

// Module Management API Routes
Route::prefix('modules')->middleware(['api.unified-auth'])->group(function () {
    Route::get('/', [ModuleController::class, 'index']);
    Route::get('/discover', [ModuleController::class, 'discover']);
    Route::post('/install', [ModuleController::class, 'install']);
    Route::get('/{module}', [ModuleController::class, 'show']);
    Route::post('/{module}/enable', [ModuleController::class, 'enable']);
    Route::post('/{module}/disable', [ModuleController::class, 'disable']);
    Route::get('/{module}/health', [ModuleController::class, 'checkHealth']);
    Route::get('/{module}/dependencies', [ModuleController::class, 'checkDependencies']);
});

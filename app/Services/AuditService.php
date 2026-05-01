<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event.
     */
    public static function log(
        string $eventType,
        ?Model $auditable = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        string $severity = 'info'
    ): AuditLog {
        $request = Request::instance();

        return AuditLog::create([
            'user_id' => Auth::id(),
            'event_type' => $eventType,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => $description,
            'old_values' => ! empty($oldValues) ? $oldValues : null,
            'new_values' => ! empty($newValues) ? $newValues : null,
            'severity' => $severity,
            'request_id' => $request->attributes->get('request_id'),
        ]);
    }

    /**
     * Log a login event.
     */
    public static function logLogin(?int $userId = null): AuditLog
    {
        $user = $userId ? User::find($userId) : Auth::user();

        return self::log(
            eventType: 'login',
            description: 'User logged in: '.$user?->email,
            severity: 'info'
        );
    }

    /**
     * Log a failed login attempt.
     */
    public static function logFailedLogin(string $email): AuditLog
    {
        return self::log(
            eventType: 'login_failed',
            description: 'Failed login attempt for: '.$email,
            severity: 'warning'
        );
    }

    /**
     * Log a logout event.
     */
    public static function logLogout(): AuditLog
    {
        return self::log(
            eventType: 'logout',
            description: 'User logged out: '.Auth::user()?->email,
            severity: 'info'
        );
    }

    /**
     * Log a model creation.
     */
    public static function logCreate(Model $model, ?string $description = null): AuditLog
    {
        return self::log(
            eventType: 'create',
            auditable: $model,
            description: $description ?? class_basename($model).' created',
            newValues: $model->getAttributes(),
            severity: 'info'
        );
    }

    /**
     * Log a model update.
     */
    public static function logUpdate(Model $model, array $oldValues, ?string $description = null): AuditLog
    {
        return self::log(
            eventType: 'update',
            auditable: $model,
            description: $description ?? class_basename($model).' updated',
            oldValues: $oldValues,
            newValues: $model->getAttributes(),
            severity: 'info'
        );
    }

    /**
     * Log a model deletion.
     */
    public static function logDelete(Model $model, ?string $description = null): AuditLog
    {
        return self::log(
            eventType: 'delete',
            auditable: $model,
            description: $description ?? class_basename($model).' deleted',
            oldValues: $model->getAttributes(),
            severity: 'warning'
        );
    }

    /**
     * Log a security event.
     */
    public static function logSecurityEvent(
        string $description,
        ?Model $auditable = null,
        string $severity = 'critical'
    ): AuditLog {
        return self::log(
            eventType: 'security',
            auditable: $auditable,
            description: $description,
            severity: $severity
        );
    }

    /**
     * Log an SSH command execution.
     */
    public static function logSshCommand(
        string $command,
        Model $server,
        ?string $result = null,
        string $severity = 'info'
    ): AuditLog {
        return self::log(
            eventType: 'ssh_command',
            auditable: $server,
            description: 'SSH command executed: '.$command,
            newValues: [
                'command' => $command,
                'result' => $result,
            ],
            severity: $severity
        );
    }

    /**
     * Log a permission denied event.
     */
    public static function logPermissionDenied(
        string $action,
        ?Model $auditable = null
    ): AuditLog {
        return self::log(
            eventType: 'permission_denied',
            auditable: $auditable,
            description: 'Permission denied for action: '.$action,
            severity: 'warning'
        );
    }

    /**
     * Log a suspicious activity.
     */
    public static function logSuspiciousActivity(
        string $description,
        ?Model $auditable = null,
        array $metadata = []
    ): AuditLog {
        return self::log(
            eventType: 'suspicious_activity',
            auditable: $auditable,
            description: $description,
            newValues: $metadata,
            severity: 'critical'
        );
    }

    /**
     * Get recent audit logs for a user.
     */
    public static function getRecentLogsForUser(int $userId, int $limit = 50): Collection
    {
        return AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent audit logs for a model.
     */
    public static function getRecentLogsForModel(Model $model, int $limit = 50): Collection
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get critical security events.
     */
    public static function getCriticalEvents(int $limit = 100): Collection
    {
        return AuditLog::where('severity', 'critical')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up old audit logs.
     */
    public static function cleanupOldLogs(int $daysToKeep = 90): int
    {
        return AuditLog::where('created_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }
}

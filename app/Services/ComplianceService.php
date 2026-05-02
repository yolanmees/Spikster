<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\EmailAccount;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplianceService
{
    public function getDataRetentionConfig(): array
    {
        return [
            'audit_logs_days' => config('compliance.retention.audit_logs', 365),
            'metrics_days' => config('compliance.retention.metrics', 90),
            'backup_days' => config('compliance.retention.backups', 30),
            'failed_jobs_days' => config('compliance.retention.failed_jobs', 14),
            'email_logs_days' => config('compliance.retention.email_logs', 30),
        ];
    }

    public function exportUserData(User $user): array
    {
        $data = [
            'user' => $user->toArray(),
            'sites' => Site::whereHas('server', fn ($q) => $q->where('user_id', $user->id))->get()->toArray(),
            'audit_logs' => AuditLog::where('user_id', $user->id)->get()->toArray(),
        ];

        $filename = "export-user-{$user->id}-".now()->format('Ymd-His').'.json';
        $path = "exports/{$filename}";
        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));

        Log::info("User data exported for user {$user->id}: {$filename}");

        return [
            'filename' => $filename,
            'path' => Storage::path($path),
            'size' => strlen(json_encode($data)),
            'exported_at' => now()->toIso8601String(),
        ];
    }

    public function deleteUserData(User $user): array
    {
        $counts = [
            'audit_logs' => AuditLog::where('user_id', $user->id)->delete(),
            'sessions' => $user->tokens()->delete(),
        ];

        // Anonymize the user instead of full delete to maintain referential integrity
        $user->update([
            'name' => 'Deleted User',
            'email' => 'deleted-'.$user->id.'@localhost',
            'password' => bcrypt(\Illuminate\Support\Str::random(60)),
        ]);

        Log::info("User data redacted for user {$user->id}");

        return [
            'deleted_records' => $counts,
            'user_anonymized' => true,
            'redacted_at' => now()->toIso8601String(),
        ];
    }

    public function exportAuditLogs(\DateTimeInterface $from, \DateTimeInterface $to): string
    {
        $logs = AuditLog::whereBetween('created_at', [$from, $to])->get();

        $filename = "audit-export-{$from->format('Ymd')}-{$to->format('Ymd')}.csv";
        $path = storage_path("app/exports/{$filename}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        fputcsv($handle, ['ID', 'Event Type', 'Severity', 'User ID', 'IP Address', 'Description', 'Created At']);

        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id, $log->event_type, $log->severity,
                $log->user_id, $log->ip_address, $log->description,
                $log->created_at->toIso8601String(),
            ]);
        }

        fclose($handle);

        Log::info("Audit log exported: {$filename} ({$logs->count()} records)");

        return $path;
    }

    public function getPrivacyPolicy(): array
    {
        return [
            'data_collected' => ['email', 'name', 'ip_address', 'server_metrics', 'backup_files'],
            'data_retention' => $this->getDataRetentionConfig(),
            'user_rights' => ['export', 'delete', 'rectify'],
            'encryption_at_rest' => config('app.key') !== null,
            'has_export_tool' => true,
            'has_anonymization' => true,
        ];
    }

    public function applyRetentionPolicies(): array
    {
        $deleted = [];

        $auditDays = config('compliance.retention.audit_logs', 365);
        $deleted['audit_logs'] = AuditLog::where('created_at', '<', now()->subDays($auditDays))->delete();

        $failedJobsDays = config('compliance.retention.failed_jobs', 14);
        if (class_exists(\App\Models\FailedJob::class)) {
            $deleted['failed_jobs'] = \App\Models\FailedJob::where('failed_at', '<', now()->subDays($failedJobsDays))->delete();
        }

        Log::info('Retention policies applied', $deleted);

        return $deleted;
    }
}

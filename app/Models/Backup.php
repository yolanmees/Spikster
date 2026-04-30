<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'site_id',
        'server_id',
        'backup_schedule_id',
        'type',
        'filename',
        'filepath',
        'size_bytes',
        'compressed_size_bytes',
        'is_encrypted',
        'encryption_method',
        'storage_location',
        'remote_path',
        'status',
        'started_at',
        'completed_at',
        'error_message',
        'includes_database',
        'includes_files',
        'database_dump_size',
        'files_count',
        'checksum',
        'metadata',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'includes_database' => 'boolean',
        'includes_files' => 'boolean',
        'includes_email' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the site that owns this backup.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    /**
     * Get the server where backup was created.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }

    /**
     * Get the schedule that created this backup.
     */
    public function backupSchedule(): BelongsTo
    {
        return $this->belongsTo(BackupSchedule::class);
    }

    /**
     * Scope for completed backups.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed backups.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for in-progress backups.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for specific backup type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for encrypted backups.
     */
    public function scopeEncrypted($query)
    {
        return $query->where('is_encrypted', true);
    }

    /**
     * Check if backup is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if backup failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if backup is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSize(): string
    {
        return $this->formatBytes($this->size_bytes);
    }

    /**
     * Get formatted compressed size.
     */
    public function getFormattedCompressedSize(): string
    {
        return $this->formatBytes($this->compressed_size_bytes);
    }

    /**
     * Get compression ratio as percentage.
     */
    public function getCompressionRatio(): float
    {
        if ($this->size_bytes == 0) {
            return 0;
        }

        return round((1 - ($this->compressed_size_bytes / $this->size_bytes)) * 100, 1);
    }

    /**
     * Get backup duration in seconds.
     */
    public function getDuration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        $duration = $this->getDuration();

        if ($duration === null) {
            return 'N/A';
        }

        if ($duration < 60) {
            return "{$duration} seconds";
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;

        return "{$minutes} min {$seconds} sec";
    }

    /**
     * Mark backup as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark backup as completed.
     */
    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
            'error_message' => null,
        ], $data));
    }

    /**
     * Mark backup as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $error,
        ]);
    }

    /**
     * Get download URL.
     */
    public function getDownloadUrl(): string
    {
        return route('api.backups.download', ['backup_id' => $this->id]);
    }

    /**
     * Verify backup integrity using checksum.
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->checksum || !file_exists($this->filepath)) {
            return false;
        }

        $actualChecksum = hash_file('sha256', $this->filepath);

        return $actualChecksum === $this->checksum;
    }

    /**
     * Check if backup exists on storage.
     */
    public function exists(): bool
    {
        if ($this->storage_location === 'local') {
            return file_exists($this->filepath);
        }

        // For remote storage, would need to check via API
        return true;
    }

    /**
     * Get backup type badge color.
     */
    public function getTypeBadgeColor(): string
    {
        return match($this->type) {
            'full' => 'blue',
            'incremental' => 'green',
            'database' => 'purple',
            'files' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'completed' => 'green',
            'in_progress' => 'blue',
            'failed' => 'red',
            'pending' => 'yellow',
            'deleted' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Format bytes to human-readable size.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

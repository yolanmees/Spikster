<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'server_id',
        'status',
        'branch',
        'commit_hash',
        'commit_message',
        'steps',
        'error_message',
        'output_log',
        'triggered_by',
        'user_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'steps' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge([
            'status' => 'success',
            'completed_at' => now(),
            'error_message' => null,
        ], $data));
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $error,
        ]);
    }

    public function getDuration(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    public function getFormattedDuration(): string
    {
        $duration = $this->getDuration();

        if ($duration === null) {
            return 'N/A';
        }

        if ($duration < 60) {
            return "{$duration}s";
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;

        return "{$minutes}m {$seconds}s";
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'running' => 'blue',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
    }
}

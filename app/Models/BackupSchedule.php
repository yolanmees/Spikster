<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupSchedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'site_id',
        'name',
        'type',
        'frequency',
        'hour',
        'day_of_week',
        'day_of_month',
        'cron_expression',
        'storage_locations',
        'is_encrypted',
        'retention_count',
        'retention_days',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'storage_locations' => 'array',
        'is_encrypted' => 'boolean',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * Get the site that owns this schedule.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    /**
     * Get the backups created by this schedule.
     */
    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    /**
     * Scope for active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for schedules that need to run.
     */
    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->where('next_run_at', '<=', now());
    }

    /**
     * Calculate next run time based on frequency.
     */
    public function calculateNextRun(): void
    {
        $nextRun = match ($this->frequency) {
            'daily' => now()->setHour($this->hour)->setMinute(0)->setSecond(0)->addDay(),
            'weekly' => now()->setHour($this->hour)->setMinute(0)->setSecond(0)
                ->next($this->day_of_week ?? 0),
            'monthly' => now()->setHour($this->hour)->setMinute(0)->setSecond(0)
                ->setDay($this->day_of_month ?? 1)->addMonth(),
            'custom' => $this->calculateFromCron(),
            default => now()->addDay(),
        };

        $this->update(['next_run_at' => $nextRun]);
    }

    /**
     * Calculate next run from cron expression.
     */
    protected function calculateFromCron(): Carbon
    {
        // Simple cron parser - in production use a package like mtdowling/cron-expression
        // For now, default to daily
        return now()->addDay();
    }

    /**
     * Mark schedule as run.
     */
    public function markAsRun(): void
    {
        $this->update(['last_run_at' => now()]);
        $this->calculateNextRun();
    }

    /**
     * Get human-readable schedule description.
     */
    public function getScheduleDescription(): string
    {
        return match ($this->frequency) {
            'daily' => "Daily at {$this->hour}:00",
            'weekly' => 'Weekly on '.$this->getDayName()." at {$this->hour}:00",
            'monthly' => "Monthly on day {$this->day_of_month} at {$this->hour}:00",
            'custom' => "Custom: {$this->cron_expression}",
            default => 'Unknown frequency',
        };
    }

    /**
     * Get day name from day_of_week.
     */
    protected function getDayName(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return $days[$this->day_of_week ?? 0] ?? 'Unknown';
    }

    /**
     * Get retention policy description.
     */
    public function getRetentionDescription(): string
    {
        if ($this->retention_days) {
            return "Keep backups for {$this->retention_days} days";
        }

        return "Keep last {$this->retention_count} backups";
    }
}

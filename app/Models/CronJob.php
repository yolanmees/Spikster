<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * CronJob Model
 *
 * @property int $id
 * @property int $server_id
 * @property string $command
 * @property string $schedule
 * @property string|null $description
 * @property bool $enabled
 * @property string|null $output_file
 * @property bool $notify_on_error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CronJob extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'server_id',
        'site_id',
        'scope',
        'command',
        'schedule',
        'description',
        'enabled',
        'output_file',
        'notify_on_error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
        'notify_on_error' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the server that owns the cron job.
     *
     * @return BelongsTo<Server, CronJob>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the site that owns the cron job (if site-scoped).
     *
     * @return BelongsTo<Site, CronJob>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get all executions for this cron job.
     *
     * @return HasMany<CronJobExecution>
     */
    public function executions(): HasMany
    {
        return $this->hasMany(CronJobExecution::class);
    }

    /**
     * Get the latest execution for this cron job.
     */
    public function latestExecution(): ?CronJobExecution
    {
        return $this->executions()->latest('started_at')->first();
    }

    /**
     * Get the full cron line for crontab.
     */
    public function getCronLineAttribute(): string
    {
        $output = $this->output_file ?? '/dev/null';
        $line = "{$this->schedule} {$this->command} >> {$output} 2>&1";

        if (! $this->enabled) {
            $line = "# {$line}";
        }

        return $line;
    }

    /**
     * Validate cron schedule format.
     */
    public static function validateSchedule(string $schedule): bool
    {
        // Cron format: minute hour day month weekday
        $parts = explode(' ', $schedule);

        if (count($parts) !== 5) {
            return false;
        }

        return true;
    }

    /**
     * Get human-readable description of schedule.
     */
    public function getScheduleDescriptionAttribute(): string
    {
        $schedule = $this->schedule;

        // Common patterns
        $patterns = [
            '* * * * *' => 'Every minute',
            '*/5 * * * *' => 'Every 5 minutes',
            '*/15 * * * *' => 'Every 15 minutes',
            '*/30 * * * *' => 'Every 30 minutes',
            '0 * * * *' => 'Every hour',
            '0 */2 * * *' => 'Every 2 hours',
            '0 */6 * * *' => 'Every 6 hours',
            '0 0 * * *' => 'Daily at midnight',
            '0 2 * * *' => 'Daily at 2:00 AM',
            '0 0 * * 0' => 'Weekly on Sunday',
            '0 0 1 * *' => 'Monthly on the 1st',
        ];

        return $patterns[$schedule] ?? $schedule;
    }

    /**
     * Scope a query to only include enabled cron jobs.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope a query to only include disabled cron jobs.
     */
    public function scopeDisabled($query)
    {
        return $query->where('enabled', false);
    }

    /**
     * Scope a query to only include server-level cron jobs.
     */
    public function scopeServerScope($query)
    {
        return $query->where('scope', 'server');
    }

    /**
     * Scope a query to only include site-level cron jobs.
     */
    public function scopeSiteScope($query)
    {
        return $query->where('scope', 'site');
    }

    /**
     * Check if this is a site-scoped cron job.
     */
    public function isSiteScoped(): bool
    {
        return $this->scope === 'site' && $this->site_id !== null;
    }

    /**
     * Check if this is a server-scoped cron job.
     */
    public function isServerScoped(): bool
    {
        return $this->scope === 'server';
    }
}

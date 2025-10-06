<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailQuotaUsage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_quota_usage';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email_account_id',
        'used_mb',
        'messages_count',
        'checked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'used_mb' => 'integer',
        'messages_count' => 'integer',
        'checked_at' => 'datetime',
    ];

    /**
     * Get the email account that owns the quota usage.
     */
    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    /**
     * Get usage percentage based on account quota.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->emailAccount->quota_mb === 0) {
            return 0; // Unlimited
        }

        return round(($this->used_mb / $this->emailAccount->quota_mb) * 100, 2);
    }

    /**
     * Check if usage is over warning threshold (80%).
     */
    public function isWarning(): bool
    {
        return $this->percentage >= 80 && $this->percentage < 95;
    }

    /**
     * Check if usage is critical (95%).
     */
    public function isCritical(): bool
    {
        return $this->percentage >= 95;
    }
}

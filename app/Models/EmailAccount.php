<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;

class EmailAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_id',
        'email',
        'password',
        'quota_mb',
        'spam_filter',
        'spam_score',
        'antivirus',
        'active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'spam_filter' => 'boolean',
        'spam_score' => 'decimal:1',
        'antivirus' => 'boolean',
        'active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the site that owns the email account.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the aliases for the email account.
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(EmailAlias::class);
    }

    /**
     * Get the autoresponder for the email account.
     */
    public function autoresponder(): HasOne
    {
        return $this->hasOne(EmailAutoresponder::class);
    }

    /**
     * Get the quota usage for the email account.
     */
    public function quotaUsage(): HasOne
    {
        return $this->hasOne(EmailQuotaUsage::class)->latestOfMany('checked_at');
    }

    /**
     * Get the email username (part before @).
     */
    public function getUsernameAttribute(): string
    {
        return explode('@', $this->email)[0];
    }

    /**
     * Get the email domain (part after @).
     */
    public function getDomainAttribute(): string
    {
        return explode('@', $this->email)[1];
    }

    /**
     * Set the password attribute (auto-hash).
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Check if quota is exceeded.
     */
    public function isQuotaExceeded(): bool
    {
        if ($this->quota_mb === 0) {
            return false; // Unlimited quota
        }

        $usage = $this->quotaUsage;
        return $usage && $usage->used_mb >= $this->quota_mb;
    }

    /**
     * Get quota usage percentage.
     */
    public function getQuotaPercentage(): float
    {
        if ($this->quota_mb === 0) {
            return 0; // Unlimited
        }

        $usage = $this->quotaUsage;
        if (!$usage) {
            return 0;
        }

        return round(($usage->used_mb / $this->quota_mb) * 100, 2);
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to filter by domain.
     */
    public function scopeByDomain($query, string $domain)
    {
        return $query->where('email', 'like', "%@{$domain}");
    }
}

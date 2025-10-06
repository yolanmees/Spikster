<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDkimKey extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_id',
        'domain',
        'selector',
        'private_key',
        'public_key',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'private_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the site that owns the DKIM key.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the DNS record name for DKIM.
     */
    public function getDnsRecordNameAttribute(): string
    {
        return "{$this->selector}._domainkey.{$this->domain}";
    }

    /**
     * Get the formatted DNS TXT record value.
     */
    public function getDnsRecordValueAttribute(): string
    {
        return "v=DKIM1; k=rsa; p={$this->public_key}";
    }

    /**
     * Scope a query to only include active keys.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

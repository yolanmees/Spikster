<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'domain_id',
        'site_id',
        'domain',
        'is_primary',
        'server_id',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the site that owns the domain.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    /**
     * Get the server for this domain.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }

    /**
     * Get the DNS records for the domain.
     */
    public function dnsRecords(): HasMany
    {
        return $this->hasMany(DnsRecord::class, 'domain_id', 'domain_id');
    }

    /**
     * Scope to get only primary domains.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get only alias domains.
     */
    public function scopeAliases($query)
    {
        return $query->where('is_primary', false);
    }
}

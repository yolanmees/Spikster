<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DnsRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'domain_id',
        'site_id',
        'ttl',
        'zone',
        'type',
        'value',
        'priority',
    ];

    protected $casts = [
        'ttl' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Get the domain that owns the DNS record.
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id', 'domain_id');
    }

    /**
     * Get the site that owns the DNS record (for backward compatibility).
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }
}

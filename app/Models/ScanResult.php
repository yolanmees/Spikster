<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    protected $fillable = [
        'server_id',
        'site_id',
        'type',
        'status',
        'findings',
        'findings_count',
        'files_scanned',
        'scanned_by',
        'scanned_at',
    ];

    protected $casts = [
        'findings' => 'array',
        'scanned_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }
}

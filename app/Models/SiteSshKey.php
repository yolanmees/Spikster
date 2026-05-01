<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSshKey extends Model
{
    protected $table = 'site_ssh_keys';

    protected $fillable = [
        'site_id',
        'label',
        'public_key',
        'fingerprint',
    ];

    protected $hidden = [
        'public_key',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }
}

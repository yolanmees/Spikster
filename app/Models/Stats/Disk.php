<?php

namespace App\Models\Stats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disk extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'stats_disks';
    // fillable
    protected $fillable = [
        "time_since_update",
        "disk_name",
        "read_count",
        "write_count",
        "read_bytes",
        "write_bytes",
        "key"
    ];
}

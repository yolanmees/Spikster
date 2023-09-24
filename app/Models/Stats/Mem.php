<?php

namespace App\Models\Stats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mem extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'stats_mems';
    // fillable
    protected $fillable = [
        "total",
        "available",
        "percent",
        "used",
        "free",
        "active",
        "inactive",
        "buffers",
        "cached",
        "shared",
    ];
}

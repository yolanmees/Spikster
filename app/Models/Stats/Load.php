<?php

namespace App\Models\Stats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'stats_loads';
    // fillable
    protected $fillable = [
        "min1",
        "min5",
        "min15",
        "cpucore",
    ];
}

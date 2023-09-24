<?php

namespace App\Models\Stats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpu extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'stats_cpus';
    // fillable
    protected $fillable = [
        "total",
        "user",
        "nice",
        "system",
        "idle",
        "iowait",
        "irq",
        "softirq",
        "steal",
        "guest",
        "guest_nice",
        "time_since_update",
        "cpucore",
        "ctx_switches",
        "interrupts",
        "soft_interrupts",
        "syscalls"
    ];

}

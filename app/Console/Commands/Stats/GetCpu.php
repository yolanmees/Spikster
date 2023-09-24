<?php

namespace App\Console\Commands\Stats;

use Illuminate\Console\Command;
use App\Models\Stats\Cpu;

class GetCpu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spikster:stats-get-cpu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cpu = json_decode(file_get_contents('http://localhost:61208/api/3/cpu'), true);
        Cpu::create([
            "total" => $cpu["total"],
            "user" => $cpu["user"],
            "nice" => $cpu["nice"],
            "system" => $cpu["system"],
            "idle" => $cpu["idle"],
            "iowait" => $cpu["iowait"],
            "irq" => $cpu["irq"],
            "softirq" => $cpu["softirq"],
            "steal" => $cpu["steal"],
            "guest" => $cpu["guest"],
            "guest_nice" => $cpu["guest_nice"],
            "time_since_update" => $cpu["time_since_update"],
            "cpucore" => $cpu["cpucore"],
            "ctx_switches" => $cpu["ctx_switches"],
            "interrupts" => $cpu["interrupts"],
            "soft_interrupts" => $cpu["soft_interrupts"],
            "syscalls" => $cpu["syscalls"]
        ]);
    }
}

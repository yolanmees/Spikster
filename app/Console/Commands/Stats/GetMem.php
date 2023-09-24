<?php

namespace App\Console\Commands\Stats;

use Illuminate\Console\Command;
use App\Models\Stats\Mem;

class GetMem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spikster:stats-get-mem';

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
        $mem = json_decode(file_get_contents('http://localhost:61208/api/3/mem'), true);
        Mem::create([
            "total" => $mem["total"],
            "available" => $mem["available"],
            "used" => $mem["used"],
            "free" => $mem["free"],
            "active" => $mem["active"],
            "inactive" => $mem["inactive"],
            "buffers" => $mem["buffers"],
            "cached" => $mem["cached"],
            "shared" => $mem["shared"]
        ]);
    }
}

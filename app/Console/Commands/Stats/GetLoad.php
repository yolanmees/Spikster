<?php

namespace App\Console\Commands\Stats;

use Illuminate\Console\Command;
use App\Models\Stats\Load;

class GetLoad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spikster:stats-get-load';

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
        $load = json_decode(file_get_contents('http://localhost:61208/api/4/load'), true);
        Load::create([
            "min1" => $load["min1"],
            "min5" => $load["min5"],
            "min15" => $load["min15"],
            "cpucore" => $load["cpucore"],
        ]);
    }
}

<?php

namespace App\Console\Commands\Stats;

use Illuminate\Console\Command;
use App\Models\Stats\Disk;

class GetDisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spikster:stats-get-disk';

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
        $disks = json_decode(file_get_contents('http://localhost:61208/api/4/diskio'), true);
        foreach ($disks as $disk) {
            Disk::create([
                "time_since_update" => $disk["time_since_update"],
                "disk_name" => $disk["disk_name"],
                "read_count" => $disk["read_count"],
                "write_count" => $disk["write_count"],
                "read_bytes" => $disk["read_bytes"],
                "write_bytes" => $disk["write_bytes"],
                "key" => $disk["key"]
            ]);
        }
    }
}

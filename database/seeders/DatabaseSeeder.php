<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user — no default password, must complete setup wizard
        User::firstOrCreate(
            ['email' => 'administrator@localhost'],
            [
                'name'     => 'admin',
                'password' => Hash::make(bin2hex(random_bytes(32))), // random, unusable until wizard runs
            ]
        );

        // Panel server entry — reads from env vars set by go.sh
        $serverId = env('PANEL_SERVER_ID');
        $serverIp = env('PANEL_SERVER_IP');
        $serverPass = env('PANEL_SERVER_PASS');
        $serverDb = env('PANEL_SERVER_DB');

        if ($serverId && $serverIp) {
            Server::firstOrCreate(
                ['server_id' => $serverId],
                [
                    'name'     => 'This VPS!',
                    'ip'       => $serverIp,
                    'password' => $serverPass ?? '',
                    'database' => $serverDb ?? '',
                    'default'  => true,
                    'status'   => 1,
                    'cron'     => ' ',
                ]
            );
        }
    }
}

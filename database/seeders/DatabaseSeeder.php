<?php

namespace Database\Seeders;

use App\Models\Auth;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Auth::query()->truncate();

        Auth::create([
            'username' => config('cipi.username'),
            'password' => Hash::make(config('cipi.password')),
            'apikey' => Str::random(48)
        ]);

        User::create([
            'name' => 'admin',
            'email' => 'admin@localhost',
            'password' => Hash::make('password'),
        ]);

        Server::create([
            'server_id' => strtolower('nze3otq0ztc0zjfjztrimzu2mzaxm2y1'),
            'name' => 'This VPS!',
            'ip' => '206.189.96.76',
            'password' => strtolower('ymq4mgyynjy1m2m1ndgyyze3zgy3ogzh'),
            'database' => strtolower('mgfjodm0nmi2ytuxnmu4yta1m2flyzkx'),
            'default' => 1,
            'cron' => ' '
        ]);

        return true;
    }
}

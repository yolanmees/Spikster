<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool restart(string $service)
 * @method static bool start(string $service)
 * @method static bool stop(string $service)
 * @method static string status(string $service)
 * @method static bool isAvailable()
 *
 * @see \App\Services\DaemonService
 */
class SpiksterDaemon extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\DaemonService::class;
    }
}

<?php

namespace App\Facades;

use App\Services\DaemonService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool restart(string $service)
 * @method static bool start(string $service)
 * @method static bool stop(string $service)
 * @method static string status(string $service)
 * @method static bool isAvailable()
 *
 * @see DaemonService
 */
class SpiksterDaemon extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DaemonService::class;
    }
}

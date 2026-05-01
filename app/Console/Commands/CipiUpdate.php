<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @deprecated This command applied a one-time 2021 patch (build 202112181).
 * All servers have long since been updated. Command is kept as a no-op stub
 * so any cron entries referencing `cipi:update` don't break.
 */
class CipiUpdate extends Command
{
    protected $signature = 'cipi:update';

    protected $description = '[deprecated] Legacy 2021 patch — no-op';

    public function handle(): int
    {
        $this->info('cipi:update is deprecated and does nothing. Use spikster:logrotate for maintenance tasks.');

        return 0;
    }
}

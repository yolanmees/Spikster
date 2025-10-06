<?php

namespace App\Events;

use App\Models\CronJobExecution;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CronJobFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CronJobExecution $execution;

    /**
     * Create a new event instance.
     */
    public function __construct(CronJobExecution $execution)
    {
        $this->execution = $execution;
    }
}

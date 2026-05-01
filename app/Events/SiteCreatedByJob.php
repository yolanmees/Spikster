<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SiteCreatedByJob implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $domain,
    ) {}

    /**
     * Broadcast on a private channel per user so only the creating user gets the event.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('sites.'.$this->userId);
    }

    /**
     * Event name used by Echo to subscribe to.
     */
    public function broadcastAs(): string
    {
        return 'site.created';
    }

    public function broadcastWith(): array
    {
        return ['domain' => $this->domain];
    }
}

<?php

namespace App\Jobs;

use App\Events\SiteCreatedByJob;
use App\Services\SiteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 1;

    public function __construct(
        public array $data,
        public int $userId = 0,
    ) {}

    public function handle(SiteService $siteService): void
    {
        try {
            $siteService->createSite($this->data);

            // Broadcast to the creating user so their UI updates without polling.
            if ($this->userId > 0) {
                SiteCreatedByJob::dispatch($this->userId, $this->data['domain']);
            }
        } catch (\Throwable $e) {
            Log::error('CreateSiteJob failed: '.$e->getMessage());
            throw $e;
        }
    }
}

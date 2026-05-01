<?php

namespace App\Jobs;

use App\Models\Site;
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

    public function __construct(public array $data) {}

    public function handle(SiteService $siteService): void
    {
        try {
            $siteService->createSite($this->data);
        } catch (\Throwable $e) {
            Log::error('CreateSiteJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

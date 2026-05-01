<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Notifications\SiteDeployFailedNotification;
use App\Services\DeploymentService;
use App\Services\ModuleHookManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeploySite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(
        public Deployment $deployment,
    ) {}

    public function handle(DeploymentService $deploymentService, ModuleHookManager $hooks): void
    {
        $site = $this->deployment->site;

        $this->deployment->markAsStarted();

        $hooks->doAction('site.before_deploy', $site, $this->deployment);

        try {
            $result = $deploymentService->deploySite($site);

            if ($result['success']) {
                $hooks->doAction('site.after_deploy', $site, $this->deployment, $result);

                $this->deployment->markAsCompleted([
                    'steps' => $result['steps'],
                ]);
            } else {
                $this->deployment->markAsFailed($result['error'] ?? 'Unknown error');

                $site->server->user?->notify(new SiteDeployFailedNotification(
                    $site,
                    $result['error'] ?? null,
                    $site->branch
                ));
            }
        } catch (\Throwable $e) {
            Log::error('DeploySite job failed: '.$e->getMessage(), [
                'site_id' => $site->site_id,
                'deployment_id' => $this->deployment->id,
            ]);

            $this->deployment->markAsFailed($e->getMessage());

            try {
                $site->server->user?->notify(new SiteDeployFailedNotification(
                    $site,
                    $e->getMessage(),
                    $site->branch
                ));
            } catch (\Throwable $notifyError) {
                Log::error('Failed to send deploy notification: '.$notifyError->getMessage());
            }
        }
    }
}

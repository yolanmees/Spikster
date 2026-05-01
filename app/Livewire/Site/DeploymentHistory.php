<?php

namespace App\Livewire\Site;

use App\Jobs\DeploySite;
use App\Models\Deployment;
use App\Models\Site;
use App\Services\DeploymentService;
use Livewire\Component;

class DeploymentHistory extends Component
{
    public string $siteId;

    public bool $deploying = false;

    public bool $hasRepo = false;

    protected function getSite(): Site
    {
        return Site::where('site_id', $this->siteId)->firstOrFail();
    }

    public function triggerDeploy(): void
    {
        $site = $this->getSite();

        if (! $site->hasRepository()) {
            $this->dispatch('notify', message: 'Configure a repository first', type: 'error');

            return;
        }

        $this->deploying = true;

        DeploySite::dispatch(
            Deployment::create([
                'site_id' => $site->site_id,
                'server_id' => $site->server_id,
                'status' => 'pending',
                'branch' => $site->branch,
                'triggered_by' => 'user',
                'user_id' => auth()->id(),
            ])
        );

        $this->dispatch('notify', message: 'Deployment queued', type: 'success');
        $this->deploying = false;
    }

    public function rollback(int $deploymentId): void
    {
        $site = $this->getSite();
        $deployment = Deployment::findOrFail($deploymentId);

        if (! $deployment->isSuccessful() || ! $deployment->commit_hash) {
            $this->dispatch('notify', message: 'Cannot rollback this deployment', type: 'error');

            return;
        }

        try {
            app(DeploymentService::class)->rollback($site, $deployment->commit_hash);

            DeploySite::dispatch(
                Deployment::create([
                    'site_id' => $site->site_id,
                    'server_id' => $site->server_id,
                    'status' => 'pending',
                    'branch' => $site->branch,
                    'commit_hash' => $deployment->commit_hash,
                    'triggered_by' => 'user',
                    'user_id' => auth()->id(),
                ])
            );

            $this->dispatch('notify', message: 'Rollback queued', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $site = Site::where('site_id', $this->siteId)->first();
        $this->hasRepo = $site && $site->hasRepository();

        $deployments = Deployment::where('site_id', $this->siteId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('livewire.site.deployment-history', [
            'deployments' => $deployments,
        ]);
    }
}

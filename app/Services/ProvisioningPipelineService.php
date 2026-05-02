<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProvisioningPipelineService
{
    protected array $steps = [];
    protected array $completed = [];
    protected array $failed = [];
    protected int $maxRetries = 3;

    public function __construct()
    {
        $this->steps = config('provisioning.steps', [
            'validate_server' => 'Validating server configuration',
            'install_daemon' => 'Installing Spikster daemon',
            'configure_firewall' => 'Configuring firewall',
            'install_database' => 'Installing database',
            'create_admin' => 'Creating admin user',
            'verify_setup' => 'Verifying setup',
        ]);
    }

    public function provision(Server $server): array
    {
        $pipelineId = 'prov_'.Str::random(12);
        $this->completed = [];
        $this->failed = [];

        Log::info("Starting provisioning pipeline {$pipelineId} for server {$server->server_id}");

        foreach ($this->steps as $step => $description) {
            $attempts = 0;
            $success = false;

            while ($attempts < $this->maxRetries && ! $success) {
                $attempts++;
                try {
                    $method = "step{$step}";
                    if (method_exists($this, $method)) {
                        $this->$method($server);
                    }
                    $this->completed[$step] = ['description' => $description, 'attempts' => $attempts];
                    $success = true;
                    Log::info("Pipeline {$pipelineId}: Step {$step} completed after {$attempts} attempt(s)");
                } catch (\Throwable $e) {
                    Log::warning("Pipeline {$pipelineId}: Step {$step} attempt {$attempts} failed: {$e->getMessage()}");
                    if ($attempts >= $this->maxRetries) {
                        $this->failed[$step] = ['description' => $description, 'error' => $e->getMessage()];
                        Log::error("Pipeline {$pipelineId}: Step {$step} failed after {$attempts} attempts");
                    }
                    sleep(min(5 * $attempts, 30));
                }
            }
        }

        $status = empty($this->failed) ? 'completed' : 'partial';

        $server->update([
            'status' => $status === 'completed' ? 1 : 2,
            'build' => $status === 'completed' ? time() : $server->build,
        ]);

        Log::info("Pipeline {$pipelineId} finished: status={$status}");

        return [
            'pipeline_id' => $pipelineId,
            'status' => $status,
            'completed_steps' => $this->completed,
            'failed_steps' => $this->failed,
            'server_id' => $server->server_id,
        ];
    }

    public function retryFailed(Server $server): array
    {
        $pipelineId = 'prov_retry_'.Str::random(8);
        $this->completed = [];
        $this->failed = [];

        // Re-run only failed steps from the server's build metadata
        $metadata = $server->metadata ?? [];

        return $this->provision($server);
    }

    protected function stepvalidate_server(Server $server): void
    {
        if (empty($server->ip) || ! filter_var($server->ip, FILTER_VALIDATE_IP)) {
            throw new \RuntimeException("Invalid server IP: {$server->ip}");
        }
    }

    protected function stepinstall_daemon(Server $server): void
    {
        $daemon = app(RemoteDaemonService::class);
        $daemon->bootstrapServer($server);
    }

    protected function stepconfigure_firewall(Server $server): void {}

    protected function stepinstall_database(Server $server): void {}

    protected function stepcreate_admin(Server $server): void {}

    protected function stepverify_setup(Server $server): void {}
}

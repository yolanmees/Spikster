<?php

namespace App\Livewire\Site;

use App\Models\Site;
use App\Services\DaemonService;
use Livewire\Component;

class SupervisorManager extends Component
{
    public string $siteId;

    public array $processes = [];

    public ?string $logOutput = null;

    public bool $loading = true;

    protected function getSite(): Site
    {
        return Site::where('site_id', $this->siteId)->firstOrFail();
    }

    public function refresh(DaemonService $daemon): void
    {
        $this->loading = true;
        $site = $this->getSite();

        try {
            $result = $daemon->supervisorCtl('status');
            $output = $result['output'] ?? '';

            $lines = explode("\n", trim($output));
            $this->processes = [];

            foreach ($lines as $line) {
                if (preg_match('/^(\S+)\s+(\S+)\s+(.+)$/', $line, $m)) {
                    $status = strtolower($m[2]);
                    $this->processes[] = [
                        'name' => $m[1],
                        'status' => match (true) {
                            str_contains($status, 'running') => 'running',
                            str_contains($status, 'stopped') => 'stopped',
                            str_contains($status, 'fatal') => 'failed',
                            str_contains($status, 'backoff') => 'backoff',
                            default => $status,
                        },
                        'detail' => $m[3],
                    ];
                }
            }
        } catch (\Throwable $e) {
            $this->processes = [];
        }

        $this->loading = false;
    }

    public function start(DaemonService $daemon, string $process): void
    {
        $daemon->supervisorCtl('start', $process);
        $this->refresh($daemon);
    }

    public function stop(DaemonService $daemon, string $process): void
    {
        $daemon->supervisorCtl('stop', $process);
        $this->refresh($daemon);
    }

    public function restart(DaemonService $daemon, string $process): void
    {
        $daemon->supervisorCtl('restart', $process);
        $this->refresh($daemon);
    }

    public function tail(DaemonService $daemon, string $process): void
    {
        $result = $daemon->supervisorCtl('tail', $process);
        $this->logOutput = $result['output'] ?? 'No log output';
    }

    public function closeLog(): void
    {
        $this->logOutput = null;
    }

    public function render()
    {
        if ($this->loading) {
            $this->refresh(app(DaemonService::class));
        }

        return view('livewire.site.supervisor-manager', [
            'hasCommand' => $this->getSite()->supervisor ? true : false,
        ]);
    }
}

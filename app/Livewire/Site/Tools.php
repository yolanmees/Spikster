<?php

namespace App\Livewire\Site;

use App\Models\Site;
use App\Services\SiteToolsService;
use Livewire\Component;

class Tools extends Component
{
    public Site $site;
    public array $detection = [];
    public bool $detected = false;

    // Artisan
    public string $artisanCommand = '';
    public string $artisanOutput = '';
    public array $quickCommands = [
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear',
        'optimize:clear',
        'optimize',
        'migrate --force',
        'queue:restart',
        'schedule:run',
    ];

    // .env
    public string $envContent = '';
    public bool $editingEnv = false;

    // Log
    public string $logContent = '';
    public int $logLines = 50;

    // Maintenance
    public bool $maintenanceMode = false;
    public string $maintenanceSecret = '';

    // Queue
    public string $queueStatus = '';

    // Schedule
    public string $scheduleList = '';

    public bool $loading = true;

    public function mount(SiteToolsService $tools)
    {
        $this->detection = $tools->detect($this->site);
        $this->detected = $this->detection['type'] === 'laravel';
    }

    public function runArtisan(SiteToolsService $tools)
    {
        if (empty($this->artisanCommand)) return;

        $this->artisanOutput = '';
        $result = $tools->artisan($this->site, $this->artisanCommand);
        $this->artisanOutput = $result['output'] ?? 'Command failed';
    }

    public function quickArtisan(SiteToolsService $tools, string $command)
    {
        $this->artisanCommand = $command;
        $this->artisanOutput = '';
        $result = $tools->artisan($this->site, $command);
        $this->artisanOutput = $result['output'] ?? 'Command failed';
    }

    public function loadEnv(SiteToolsService $tools)
    {
        $content = $tools->readEnv($this->site);
        if ($content !== null) {
            $this->envContent = $content;
            $this->editingEnv = true;
        }
    }

    public function saveEnv(SiteToolsService $tools)
    {
        if ($tools->writeEnv($this->site, $this->envContent)) {
            $this->editingEnv = false;
            $this->dispatch('env-saved');
        }
    }

    public function loadLog(SiteToolsService $tools)
    {
        $result = $tools->tailLog($this->site, $this->logLines);
        $this->logContent = $result['content'];
    }

    public function toggleMaintenance(SiteToolsService $tools)
    {
        $down = !$this->maintenanceMode;
        $secret = $down ? $this->maintenanceSecret : '';
        $result = $tools->maintenanceMode($this->site, $down, $secret);
        $this->maintenanceMode = $down;
        $this->dispatch('maintenance-toggled');
    }

    public function loadQueueStatus(SiteToolsService $tools)
    {
        $this->queueStatus = $tools->queueStatus($this->site);
    }

    public function loadSchedule(SiteToolsService $tools)
    {
        $this->scheduleList = $tools->scheduleList($this->site);
    }

    public function render()
    {
        return view('livewire.site.tools');
    }
}

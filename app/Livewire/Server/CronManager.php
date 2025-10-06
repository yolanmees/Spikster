<?php

namespace App\Livewire\Server;

use App\Models\CronJob;
use App\Models\Server;
use App\Services\CronService;
use Livewire\Component;
use Livewire\Attributes\On;

class CronManager extends Component
{
    public Server $server;
    public $cronJobs = [];
    public $sites = [];
    
    // Form fields
    public $editingId = null;
    public $scope = 'server';
    public $site_id = null;
    public $command = '';
    public $schedule = '';
    public $description = '';
    public $enabled = true;
    public $output_file = '';
    public $notify_on_error = false;
    
    // UI state
    public $showForm = false;
    public $showPreview = false;
    public $showTemplates = false;
    
    // Predefined schedules
    public $presetSchedules = [
        '* * * * *' => 'Every minute',
        '*/5 * * * *' => 'Every 5 minutes',
        '*/15 * * * *' => 'Every 15 minutes',
        '*/30 * * * *' => 'Every 30 minutes',
        '0 * * * *' => 'Every hour',
        '0 */2 * * *' => 'Every 2 hours',
        '0 */6 * * *' => 'Every 6 hours',
        '0 0 * * *' => 'Daily at midnight',
        '0 2 * * *' => 'Daily at 2:00 AM',
        '0 0 * * 0' => 'Weekly on Sunday',
        '0 0 1 * *' => 'Monthly on the 1st',
        'custom' => 'Custom schedule',
    ];

    // Cron job templates
    public $templates = [
        'laravel_scheduler' => [
            'name' => 'Laravel Task Scheduler',
            'description' => 'Run Laravel\'s task scheduler',
            'command' => 'cd /home/{site}/web && php artisan schedule:run',
            'schedule' => '* * * * *',
            'scope' => 'site',
        ],
        'laravel_queue' => [
            'name' => 'Laravel Queue Worker',
            'description' => 'Process Laravel queue jobs',
            'command' => 'cd /home/{site}/web && php artisan queue:work --stop-when-empty',
            'schedule' => '* * * * *',
            'scope' => 'site',
        ],
        'database_backup' => [
            'name' => 'MySQL Database Backup',
            'description' => 'Daily database backup',
            'command' => 'mysqldump -u {user} -p\'{password}\' {database} > /backups/db_$(date +\%Y\%m\%d).sql',
            'schedule' => '0 2 * * *',
            'scope' => 'site',
        ],
        'log_rotation' => [
            'name' => 'Log Rotation',
            'description' => 'Weekly log cleanup',
            'command' => 'find /var/log/app -name "*.log" -mtime +7 -delete',
            'schedule' => '0 0 * * 0',
            'scope' => 'server',
        ],
        'ssl_renewal' => [
            'name' => 'SSL Certificate Renewal Check',
            'description' => 'Check and renew SSL certificates',
            'command' => 'certbot renew --quiet',
            'schedule' => '0 0,12 * * *',
            'scope' => 'server',
        ],
        'site_health_check' => [
            'name' => 'Website Health Check',
            'description' => 'Monitor website availability',
            'command' => 'curl -s https://{domain}/health >> /var/log/health.log',
            'schedule' => '*/5 * * * *',
            'scope' => 'site',
        ],
    ];
    
    protected $rules = [
        'scope' => 'required|in:server,site',
        'site_id' => 'nullable|required_if:scope,site|exists:sites,id',
        'command' => 'required|string|max:1000',
        'schedule' => 'required|string|max:100',
        'description' => 'nullable|string|max:255',
        'enabled' => 'boolean',
        'output_file' => 'nullable|string|max:255',
        'notify_on_error' => 'boolean',
    ];

    public function mount(Server $server)
    {
        $this->server = $server;
        $this->sites = $server->sites;
        $this->loadCronJobs();
    }

    public function loadCronJobs()
    {
        $this->cronJobs = $this->server->cronJobs()->orderBy('created_at', 'desc')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $cronJob = CronJob::findOrFail($id);
        
        $this->editingId = $cronJob->id;
        $this->scope = $cronJob->scope;
        $this->site_id = $cronJob->site_id;
        $this->command = $cronJob->command;
        $this->schedule = $cronJob->schedule;
        $this->description = $cronJob->description ?? '';
        $this->enabled = $cronJob->enabled;
        $this->output_file = $cronJob->output_file ?? '';
        $this->notify_on_error = $cronJob->notify_on_error;
        
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $cronJob = CronJob::findOrFail($this->editingId);
            $cronJob->update([
                'scope' => $this->scope,
                'site_id' => $this->scope === 'site' ? $this->site_id : null,
                'command' => $this->command,
                'schedule' => $this->schedule,
                'description' => $this->description,
                'enabled' => $this->enabled,
                'output_file' => $this->output_file ?: null,
                'notify_on_error' => $this->notify_on_error,
            ]);
            
            session()->flash('message', 'Cron job updated successfully.');
        } else {
            CronJob::create([
                'server_id' => $this->server->id,
                'scope' => $this->scope,
                'site_id' => $this->scope === 'site' ? $this->site_id : null,
                'command' => $this->command,
                'schedule' => $this->schedule,
                'description' => $this->description,
                'enabled' => $this->enabled,
                'output_file' => $this->output_file ?: null,
                'notify_on_error' => $this->notify_on_error,
            ]);
            
            session()->flash('message', 'Cron job created successfully.');
        }

        // Sync to server
        $this->syncToServer();
        
        $this->resetForm();
        $this->loadCronJobs();
    }

    public function delete($id)
    {
        $cronJob = CronJob::findOrFail($id);
        $cronJob->delete();
        
        session()->flash('message', 'Cron job deleted successfully.');
        
        // Sync to server
        $this->syncToServer();
        
        $this->loadCronJobs();
    }

    public function toggle($id)
    {
        $cronJob = CronJob::findOrFail($id);
        $cronJob->update(['enabled' => !$cronJob->enabled]);
        
        session()->flash('message', 'Cron job ' . ($cronJob->enabled ? 'enabled' : 'disabled') . '.');
        
        // Sync to server
        $this->syncToServer();
        
        $this->loadCronJobs();
    }

    public function syncToServer()
    {
        try {
            $cronService = app(CronService::class);
            $cronService->syncCronJobsToServer($this->server);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sync cron jobs to server: ' . $e->getMessage());
        }
    }

    public function importFromServer()
    {
        try {
            $cronService = app(CronService::class);
            $imported = $cronService->importFromServer($this->server);
            
            session()->flash('message', "Imported {$imported} cron job(s) from server.");
            $this->loadCronJobs();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to import cron jobs from server: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->scope = 'server';
        $this->site_id = null;
        $this->command = '';
        $this->schedule = '';
        $this->description = '';
        $this->enabled = true;
        $this->output_file = '';
        $this->notify_on_error = false;
        $this->showForm = false;
    }

    public function useTemplate($templateKey)
    {
        if (!isset($this->templates[$templateKey])) {
            return;
        }

        $template = $this->templates[$templateKey];
        
        $this->description = $template['name'];
        $this->command = $template['command'];
        $this->schedule = $template['schedule'];
        $this->scope = $template['scope'];
        
        $this->showTemplates = false;
        $this->showForm = true;
        
        session()->flash('message', 'Template loaded. Please customize placeholders as needed.');
    }

    public function render()
    {
        return view('livewire.server.cron-manager');
    }
}

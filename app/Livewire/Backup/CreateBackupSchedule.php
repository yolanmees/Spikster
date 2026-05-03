<?php

namespace App\Livewire\Backup;

use App\Models\Site;
use App\Services\BackupService;
use Livewire\Component;

class CreateBackupSchedule extends Component
{
    public Site $site;

    public $name = '';
    public $type = 'full';
    public $frequency = 'daily';
    public $time = '02:00';
    public $day_of_week = null;
    public $day_of_month = null;
    public $cron_expression = '';
    public $storage_locations = ['local'];
    public $is_encrypted = false;
    public $retention_count = 10;
    public $retention_days = 30;
    public $is_active = true;

    public $isSubmitting = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:full,incremental,database,files',
        'frequency' => 'required|in:daily,weekly,monthly,custom',
        'time' => 'required|string',
        'day_of_week' => 'nullable|integer|min:0|max:6',
        'day_of_month' => 'nullable|integer|min:1|max:31',
        'cron_expression' => 'nullable|string',
        'storage_locations' => 'required|array',
        'is_encrypted' => 'boolean',
        'retention_count' => 'nullable|integer|min:1',
        'retention_days' => 'nullable|integer|min:1',
        'is_active' => 'boolean',
    ];

    public function mount(Site $site)
    {
        $this->site = $site;
    }

    public function render()
    {
        return view('livewire.backup.create-schedule');
    }

    public function submit()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            $validated = $this->validate();

            $backupService = app(BackupService::class);
            $backupService->createSchedule($this->site, [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'frequency' => $validated['frequency'],
                'time' => $validated['time'],
                'day_of_week' => $validated['day_of_week'],
                'day_of_month' => $validated['day_of_month'],
                'cron_expression' => $validated['cron_expression'] ?: null,
                'storage_locations' => $validated['storage_locations'],
                'is_encrypted' => $validated['is_encrypted'],
                'retention_count' => $validated['retention_count'],
                'retention_days' => $validated['retention_days'],
                'is_active' => $validated['is_active'],
            ]);

            session()->flash('success', 'Backup schedule created successfully.');

            $this->redirect(route('backups.index', $this->site));
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating schedule: '.$e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }
}

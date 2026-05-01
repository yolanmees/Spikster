<?php

namespace App\Livewire\Backup;

use App\Models\BackupSchedule;
use App\Models\Site;
use App\Services\BackupService;
use Livewire\Component;

class BackupScheduleManager extends Component
{
    public Site $site;

    public $schedules;

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $selectedSchedule = null;

    // Form fields
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
        $this->loadSchedules();
    }

    public function render()
    {
        return view('livewire.backup.backup-schedule-manager');
    }

    public function loadSchedules()
    {
        $this->schedules = BackupSchedule::where('site_id', $this->site->id)
            ->with('backups')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal($scheduleId)
    {
        $this->selectedSchedule = BackupSchedule::where('site_id', $this->site->id)->find($scheduleId);

        if ($this->selectedSchedule) {
            $this->name = $this->selectedSchedule->name;
            $this->type = $this->selectedSchedule->type;
            $this->frequency = $this->selectedSchedule->frequency;
            $this->time = $this->selectedSchedule->time;
            $this->day_of_week = $this->selectedSchedule->day_of_week;
            $this->day_of_month = $this->selectedSchedule->day_of_month;
            $this->cron_expression = $this->selectedSchedule->cron_expression ?? '';
            $this->storage_locations = $this->selectedSchedule->storage_locations ?? ['local'];
            $this->is_encrypted = $this->selectedSchedule->is_encrypted;
            $this->retention_count = $this->selectedSchedule->retention_count;
            $this->retention_days = $this->selectedSchedule->retention_days;
            $this->is_active = $this->selectedSchedule->is_active;

            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedSchedule = null;
        $this->resetForm();
    }

    public function openDeleteModal($scheduleId)
    {
        $this->selectedSchedule = BackupSchedule::where('site_id', $this->site->id)->find($scheduleId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedSchedule = null;
    }

    public function createSchedule()
    {
        $this->validate();

        $backupService = app(BackupService::class);
        $backupService->createSchedule($this->site, [
            'name' => $this->name,
            'type' => $this->type,
            'frequency' => $this->frequency,
            'time' => $this->time,
            'day_of_week' => $this->day_of_week,
            'day_of_month' => $this->day_of_month,
            'cron_expression' => $this->cron_expression ?: null,
            'storage_locations' => $this->storage_locations,
            'is_encrypted' => $this->is_encrypted,
            'retention_count' => $this->retention_count,
            'retention_days' => $this->retention_days,
            'is_active' => $this->is_active,
        ]);

        $this->closeCreateModal();
        $this->loadSchedules();
        session()->flash('message', 'Backup schedule created successfully!');
    }

    public function updateSchedule()
    {
        $this->validate();

        if (! $this->selectedSchedule) {
            return;
        }

        $backupService = app(BackupService::class);
        $backupService->updateSchedule($this->selectedSchedule, [
            'name' => $this->name,
            'type' => $this->type,
            'frequency' => $this->frequency,
            'time' => $this->time,
            'day_of_week' => $this->day_of_week,
            'day_of_month' => $this->day_of_month,
            'cron_expression' => $this->cron_expression ?: null,
            'storage_locations' => $this->storage_locations,
            'is_encrypted' => $this->is_encrypted,
            'retention_count' => $this->retention_count,
            'retention_days' => $this->retention_days,
            'is_active' => $this->is_active,
        ]);

        $this->closeEditModal();
        $this->loadSchedules();
        session()->flash('message', 'Backup schedule updated successfully!');
    }

    public function deleteSchedule()
    {
        if (! $this->selectedSchedule) {
            return;
        }

        $backupService = app(BackupService::class);
        $backupService->deleteSchedule($this->selectedSchedule, false);

        $this->closeDeleteModal();
        $this->loadSchedules();
        session()->flash('message', 'Backup schedule deleted successfully!');
    }

    public function toggleSchedule($scheduleId)
    {
        $schedule = BackupSchedule::where('site_id', $this->site->id)->find($scheduleId);

        if ($schedule) {
            $schedule->is_active = ! $schedule->is_active;
            $schedule->save();

            if ($schedule->is_active) {
                $schedule->calculateNextRun();
            }

            $this->loadSchedules();
            session()->flash('message', 'Schedule '.($schedule->is_active ? 'enabled' : 'disabled'));
        }
    }

    protected function resetForm()
    {
        $this->name = '';
        $this->type = 'full';
        $this->frequency = 'daily';
        $this->time = '02:00';
        $this->day_of_week = null;
        $this->day_of_month = null;
        $this->cron_expression = '';
        $this->storage_locations = ['local'];
        $this->is_encrypted = false;
        $this->retention_count = 10;
        $this->retention_days = 30;
        $this->is_active = true;
        $this->resetValidation();
    }
}

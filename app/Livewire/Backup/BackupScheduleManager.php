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

    protected function getListeners()
    {
        return [
            'restore-schedule' => 'restoreSchedule',
        ];
    }

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
            $this->dispatch('notify',
                message: 'Schedule '.($schedule->is_active ? 'enabled' : 'disabled'),
                type: 'success',
            );
        }
    }

    public function deleteSchedule($scheduleId)
    {
        $schedule = BackupSchedule::where('site_id', $this->site->id)->findOrFail($scheduleId);
        $name = $schedule->name;
        $schedule->delete();

        $this->loadSchedules();

        $this->dispatch('notify',
            message: 'Schedule "'.$name.'" deleted',
            type: 'success',
            undo: ['event' => 'restore-schedule', 'params' => ['scheduleId' => $scheduleId]],
        );
    }

    public function restoreSchedule($scheduleId)
    {
        $schedule = BackupSchedule::withTrashed()
            ->where('site_id', $this->site->id)
            ->findOrFail($scheduleId);

        $schedule->restore();

        $this->loadSchedules();

        $this->dispatch('notify',
            message: 'Schedule "'.$schedule->name.'" restored',
            type: 'success',
        );
    }
}

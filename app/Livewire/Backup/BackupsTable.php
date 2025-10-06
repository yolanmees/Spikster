<?php

namespace App\Livewire\Backup;

use App\Models\Backup;
use App\Models\Site;
use App\Services\BackupService;
use Livewire\Component;
use Livewire\WithPagination;

class BackupsTable extends Component
{
    use WithPagination;

    public Site $site;
    public $selectedBackup = null;
    public $showRestoreModal = false;
    public $showDeleteModal = false;
    public $deleteFile = false;

    // Filters
    public $filterType = '';
    public $filterStatus = '';
    public $search = '';

    protected $queryString = [
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount(Site $site)
    {
        $this->site = $site;
    }

    public function render()
    {
        $backups = Backup::where('site_id', $this->site->id)
            ->with(['backupSchedule', 'server'])
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('filename', 'like', '%' . $this->search . '%')
                      ->orWhere('type', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = $this->getBackupStats();

        return view('livewire.backup.backups-table', [
            'backups' => $backups,
            'stats' => $stats,
        ]);
    }

    public function getBackupStats()
    {
        $backupService = app(BackupService::class);
        return $backupService->getBackupStats($this->site);
    }

    public function createFullBackup()
    {
        $backupService = app(BackupService::class);
        $backupService->createFullBackup($this->site, [
            'include_database' => true,
            'include_files' => true,
            'include_email' => false,
        ]);

        $this->dispatch('backup-created');
        session()->flash('message', 'Full backup queued successfully!');
    }

    public function createDatabaseBackup()
    {
        $backupService = app(BackupService::class);
        $backupService->createDatabaseBackup($this->site);

        $this->dispatch('backup-created');
        session()->flash('message', 'Database backup queued successfully!');
    }

    public function openRestoreModal($backupId)
    {
        $this->selectedBackup = Backup::find($backupId);
        $this->showRestoreModal = true;
    }

    public function closeRestoreModal()
    {
        $this->showRestoreModal = false;
        $this->selectedBackup = null;
    }

    public function openDeleteModal($backupId)
    {
        $this->selectedBackup = Backup::find($backupId);
        $this->showDeleteModal = true;
        $this->deleteFile = false;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedBackup = null;
        $this->deleteFile = false;
    }

    public function confirmRestore()
    {
        if (!$this->selectedBackup) {
            return;
        }

        $backupService = app(BackupService::class);
        $backupService->restoreBackup($this->selectedBackup, [
            'restore_database' => true,
            'restore_files' => true,
            'restore_email' => false,
        ]);

        $this->closeRestoreModal();
        $this->dispatch('backup-restored');
        session()->flash('message', 'Backup restore queued successfully!');
    }

    public function confirmDelete()
    {
        if (!$this->selectedBackup) {
            return;
        }

        $backupService = app(BackupService::class);
        $backupService->deleteBackup($this->selectedBackup, $this->deleteFile);

        $this->closeDeleteModal();
        $this->dispatch('backup-deleted');
        session()->flash('message', 'Backup deleted successfully!');
    }

    public function downloadBackup($backupId)
    {
        $backup = Backup::find($backupId);

        if (!$backup || !$backup->isComplete()) {
            session()->flash('error', 'Backup file not available for download');
            return;
        }

        // Redirect to download route
        return redirect()->route('backups.download', ['backup' => $backupId]);
    }

    public function resetFilters()
    {
        $this->filterType = '';
        $this->filterStatus = '';
        $this->search = '';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
}

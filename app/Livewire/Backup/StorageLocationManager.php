<?php

namespace App\Livewire\Backup;

use App\Models\BackupStorageLocation;
use App\Services\BackupService;
use Livewire\Component;

class StorageLocationManager extends Component
{
    public $locations;

    public $showCreateModal = false;

    public $showDeleteModal = false;

    public $selectedLocation = null;

    // Form fields
    public $name = '';

    public $type = 'local';

    public $config = [];

    public $is_default = false;

    public $is_active = true;

    // S3 Config
    public $s3_bucket = '';

    public $s3_region = 'us-east-1';

    public $s3_access_key = '';

    public $s3_secret_key = '';

    public $s3_endpoint = '';

    public $s3_path = 'backups';

    // FTP/SFTP Config
    public $ftp_host = '';

    public $ftp_port = '';

    public $ftp_username = '';

    public $ftp_password = '';

    public $ftp_path = '/backups';

    public $ftp_passive = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:local,s3,ftp,sftp',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->loadLocations();
    }

    public function render()
    {
        return view('livewire.backup.storage-location-manager');
    }

    public function loadLocations()
    {
        $this->locations = BackupStorageLocation::orderBy('created_at', 'desc')->get();
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

    public function openDeleteModal($locationId)
    {
        $this->selectedLocation = BackupStorageLocation::find($locationId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedLocation = null;
    }

    public function createLocation()
    {
        $this->validate();

        // Build config based on type
        $config = $this->buildConfig();

        $backupService = app(BackupService::class);
        $backupService->createStorageLocation([
            'name' => $this->name,
            'type' => $this->type,
            'config' => $config,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ]);

        $this->closeCreateModal();
        $this->loadLocations();
        session()->flash('message', 'Storage location created successfully!');
    }

    public function testConnection($locationId)
    {
        $location = BackupStorageLocation::find($locationId);

        if (! $location) {
            return;
        }

        $backupService = app(BackupService::class);
        $success = $backupService->testStorageConnection($location);

        $this->loadLocations();

        if ($success) {
            session()->flash('message', 'Connection test successful!');
        } else {
            session()->flash('error', 'Connection test failed!');
        }
    }

    public function deleteLocation()
    {
        if (! $this->selectedLocation) {
            return;
        }

        $backupService = app(BackupService::class);
        $success = $backupService->deleteStorageLocation($this->selectedLocation);

        if (! $success) {
            session()->flash('error', 'Cannot delete default storage location');
        } else {
            session()->flash('message', 'Storage location deleted successfully!');
        }

        $this->closeDeleteModal();
        $this->loadLocations();
    }

    protected function buildConfig(): array
    {
        switch ($this->type) {
            case 's3':
                return [
                    'bucket' => $this->s3_bucket,
                    'region' => $this->s3_region,
                    'access_key' => $this->s3_access_key,
                    'secret_key' => $this->s3_secret_key,
                    'endpoint' => $this->s3_endpoint ?: null,
                    'path' => $this->s3_path,
                ];

            case 'ftp':
            case 'sftp':
                return [
                    'host' => $this->ftp_host,
                    'port' => $this->ftp_port ?: ($this->type === 'sftp' ? 22 : 21),
                    'username' => $this->ftp_username,
                    'password' => $this->ftp_password,
                    'path' => $this->ftp_path,
                    'passive' => $this->ftp_passive,
                ];

            default:
                return [];
        }
    }

    protected function resetForm()
    {
        $this->name = '';
        $this->type = 'local';
        $this->is_default = false;
        $this->is_active = true;

        // S3
        $this->s3_bucket = '';
        $this->s3_region = 'us-east-1';
        $this->s3_access_key = '';
        $this->s3_secret_key = '';
        $this->s3_endpoint = '';
        $this->s3_path = 'backups';

        // FTP/SFTP
        $this->ftp_host = '';
        $this->ftp_port = '';
        $this->ftp_username = '';
        $this->ftp_password = '';
        $this->ftp_path = '/backups';
        $this->ftp_passive = true;

        $this->resetValidation();
    }
}

<?php

namespace App\Livewire\Backup;

use App\Models\BackupStorageLocation;
use Livewire\Component;

class StorageLocationManager extends Component
{
    public bool $showForm = false;

    public ?string $editingId = null;

    public string $name = '';

    public string $type = 'local';

    public string $configPath = '/backups';

    public string $configAccessKey = '';

    public string $configSecretKey = '';

    public string $configRegion = 'us-east-1';

    public string $configBucket = '';

    public string $configEndpoint = '';

    public string $configHost = '';

    public string $configPort = '';

    public string $configUsername = '';

    public string $configPassword = '';

    public bool $isDefault = false;

    public function render()
    {
        $locations = BackupStorageLocation::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.backup.storage-location-manager', [
            'locations' => $locations,
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function edit(string $id): void
    {
        $location = BackupStorageLocation::where('user_id', auth()->id())->findOrFail($id);
        $this->editingId = $id;
        $this->name = $location->name;
        $this->type = $location->type;
        $this->isDefault = $location->is_default;
        $this->configPath = $location->config['path'] ?? '';
        $this->configAccessKey = $location->config['access_key'] ?? '';
        $this->configSecretKey = $location->config['secret_key'] ?? '';
        $this->configRegion = $location->config['region'] ?? 'us-east-1';
        $this->configBucket = $location->config['bucket'] ?? '';
        $this->configEndpoint = $location->config['endpoint'] ?? '';
        $this->configHost = $location->config['host'] ?? '';
        $this->configPort = (string) ($location->config['port'] ?? '');
        $this->configUsername = $location->config['username'] ?? '';
        $this->configPassword = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|max:255',
            'type' => 'required|in:local,s3,ftp,sftp',
            'configPath' => 'required_if:type,local|max:255',
            'configHost' => 'required_if:type,ftp,sftp|max:255',
            'configBucket' => 'required_if:type,s3|max:255',
        ]);

        $config = match ($this->type) {
            'local' => ['path' => $this->configPath],
            's3' => [
                'access_key' => $this->configAccessKey,
                'secret_key' => $this->configSecretKey,
                'region' => $this->configRegion,
                'bucket' => $this->configBucket,
                'endpoint' => $this->configEndpoint ?: null,
            ],
            'ftp', 'sftp' => [
                'host' => $this->configHost,
                'port' => (int) $this->configPort ?: ($this->type === 'sftp' ? 22 : 21),
                'username' => $this->configUsername,
                'password' => $this->configPassword,
                'path' => $this->configPath,
            ],
        };

        $data = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'type' => $this->type,
            'config' => $config,
            'is_default' => $this->isDefault,
            'is_active' => true,
        ];

        if ($this->editingId) {
            $location = BackupStorageLocation::where('user_id', auth()->id())->findOrFail($this->editingId);
            $location->update($data);
            $this->dispatch('notify', message: 'Storage location updated', type: 'success');
        } else {
            BackupStorageLocation::create($data);
            $this->dispatch('notify', message: 'Storage location created', type: 'success');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function test(string $id): void
    {
        $location = BackupStorageLocation::where('user_id', auth()->id())->findOrFail($id);
        $result = $location->testConnection();

        if ($result) {
            $this->dispatch('notify', message: 'Connection test successful', type: 'success');
        } else {
            $error = $location->fresh()->last_test_error;
            $this->dispatch('notify', message: 'Connection failed: '.($error ?? 'Unknown error'), type: 'error');
        }
    }

    public function delete(string $id): void
    {
        $location = BackupStorageLocation::where('user_id', auth()->id())->findOrFail($id);
        $location->delete();
        $this->dispatch('notify', message: 'Storage location deleted', type: 'success');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->type = 'local';
        $this->configPath = '/backups';
        $this->configAccessKey = '';
        $this->configSecretKey = '';
        $this->configRegion = 'us-east-1';
        $this->configBucket = '';
        $this->configEndpoint = '';
        $this->configHost = '';
        $this->configPort = '';
        $this->configUsername = '';
        $this->configPassword = '';
        $this->isDefault = false;
        $this->editingId = null;
    }
}

<?php

namespace App\Livewire\Ftp;

use App\Models\FtpUser;
use App\Models\Site;
use App\Services\FtpService;
use Livewire\Component;
use Livewire\WithPagination;

class FtpUsersTable extends Component
{
    use WithPagination;

    public Site $site;

    public $search = '';

    public $statusFilter = 'all'; // all, active, inactive, locked

    public $perPage = 10;

    // Modal states
    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $showPasswordModal = false;

    public $showQuotaModal = false;

    public $showConnectionInfoModal = false;

    // Form data
    public $selectedUserId;

    public $username;

    public $password;

    public $password_confirmation;

    public $home_directory;

    public $quota_mb = 1024;

    public $max_connections = 5;

    public $bandwidth_limit_kbps;

    public $require_ssl = true;

    public $allowed_ip;

    public $notes;

    public $is_active = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'ftpUserCreated' => '$refresh',
        'ftpUserUpdated' => '$refresh',
        'ftpUserDeleted' => '$refresh',
        'refreshTable' => '$refresh',
    ];

    public function mount(Site $site)
    {
        $this->site = $site;
        $this->home_directory = "/var/www/vhosts/{$site->domain}/httpdocs";
    }

    public function render(FtpService $ftpService)
    {
        $ftpUsersQuery = FtpUser::where('site_id', $this->site->site_id)
            ->with(['site', 'server']);

        // Apply search filter
        if ($this->search) {
            $ftpUsersQuery->where(function ($query) {
                $query->where('username', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%')
                    ->orWhere('home_directory', 'like', '%'.$this->search.'%');
            });
        }

        // Apply status filter
        switch ($this->statusFilter) {
            case 'active':
                $ftpUsersQuery->active();
                break;
            case 'inactive':
                $ftpUsersQuery->inactive();
                break;
            case 'locked':
                $ftpUsersQuery->locked();
                break;
        }

        $ftpUsersQuery->orderBy('created_at', 'desc');

        $ftpUsers = $ftpUsersQuery->paginate($this->perPage);

        // Get site statistics
        $statistics = $ftpService->getSiteStatistics($this->site);

        return view('livewire.ftp.ftp-users-table', [
            'ftpUsers' => $ftpUsers,
            'statistics' => $statistics,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($userId)
    {
        $ftpUser = FtpUser::findOrFail($userId);

        $this->selectedUserId = $ftpUser->ftp_user_id;
        $this->username = $ftpUser->username;
        $this->home_directory = $ftpUser->home_directory;
        $this->quota_mb = $ftpUser->quota_mb;
        $this->max_connections = $ftpUser->max_connections;
        $this->bandwidth_limit_kbps = $ftpUser->bandwidth_limit_kbps;
        $this->require_ssl = $ftpUser->require_ssl;
        $this->allowed_ip = $ftpUser->allowed_ip;
        $this->notes = $ftpUser->notes;
        $this->is_active = $ftpUser->is_active;

        $this->showEditModal = true;
    }

    public function openDeleteModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function openPasswordModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showPasswordModal = true;
    }

    public function openQuotaModal($userId)
    {
        $ftpUser = FtpUser::findOrFail($userId);
        $this->selectedUserId = $ftpUser->ftp_user_id;
        $this->quota_mb = $ftpUser->quota_mb;
        $this->showQuotaModal = true;
    }

    public function openConnectionInfoModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->showConnectionInfoModal = true;
    }

    public function createUser(FtpService $ftpService)
    {
        $this->validate([
            'username' => 'nullable|string|unique:ftp_users|max:255',
            'password' => 'required|string|min:8|confirmed',
            'home_directory' => 'required|string',
            'quota_mb' => 'required|integer|min:100|max:100000',
            'max_connections' => 'required|integer|min:1|max:20',
            'bandwidth_limit_kbps' => 'nullable|integer|min:128',
            'require_ssl' => 'required|boolean',
            'allowed_ip' => 'nullable|ip',
            'notes' => 'nullable|string|max:1000',
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters.',
            'quota_mb.min' => 'Minimum quota is 100 MB.',
            'quota_mb.max' => 'Maximum quota is 100 GB.',
        ]);

        try {
            $ftpService->createUser($this->site, [
                'username' => $this->username,
                'password' => $this->password,
                'home_directory' => $this->home_directory,
                'quota_mb' => $this->quota_mb,
                'max_connections' => $this->max_connections,
                'bandwidth_limit_kbps' => $this->bandwidth_limit_kbps,
                'require_ssl' => $this->require_ssl,
                'allowed_ip' => $this->allowed_ip,
                'notes' => $this->notes,
            ]);

            $this->showCreateModal = false;
            $this->resetForm();

            $this->dispatch('ftpUserCreated');
            session()->flash('success', 'FTP user created successfully! The user will be provisioned on the server shortly.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create FTP user: '.$e->getMessage());
        }
    }

    public function updateUser(FtpService $ftpService)
    {
        $this->validate([
            'home_directory' => 'required|string',
            'quota_mb' => 'required|integer|min:100|max:100000',
            'max_connections' => 'required|integer|min:1|max:20',
            'bandwidth_limit_kbps' => 'nullable|integer|min:128',
            'require_ssl' => 'required|boolean',
            'allowed_ip' => 'nullable|ip',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
        ]);

        try {
            $ftpUser = FtpUser::findOrFail($this->selectedUserId);

            $ftpService->updateUser($ftpUser, [
                'home_directory' => $this->home_directory,
                'quota_mb' => $this->quota_mb,
                'max_connections' => $this->max_connections,
                'bandwidth_limit_kbps' => $this->bandwidth_limit_kbps,
                'require_ssl' => $this->require_ssl,
                'allowed_ip' => $this->allowed_ip,
                'notes' => $this->notes,
                'is_active' => $this->is_active,
            ]);

            $this->showEditModal = false;
            $this->resetForm();

            $this->dispatch('ftpUserUpdated');
            session()->flash('success', 'FTP user updated successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update FTP user: '.$e->getMessage());
        }
    }

    public function deleteUser(FtpService $ftpService)
    {
        try {
            $ftpUser = FtpUser::findOrFail($this->selectedUserId);
            $ftpService->deleteUser($ftpUser);

            $this->showDeleteModal = false;
            $this->selectedUserId = null;

            $this->dispatch('ftpUserDeleted');
            session()->flash('success', 'FTP user deleted successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete FTP user: '.$e->getMessage());
        }
    }

    public function resetPassword(FtpService $ftpService)
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters.',
        ]);

        try {
            $ftpUser = FtpUser::findOrFail($this->selectedUserId);
            $ftpService->resetPassword($ftpUser, $this->password);

            $this->showPasswordModal = false;
            $this->password = '';
            $this->password_confirmation = '';

            session()->flash('success', 'Password reset successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reset password: '.$e->getMessage());
        }
    }

    public function updateQuota(FtpService $ftpService)
    {
        $this->validate([
            'quota_mb' => 'required|integer|min:100|max:100000',
        ], [
            'quota_mb.min' => 'Minimum quota is 100 MB.',
            'quota_mb.max' => 'Maximum quota is 100 GB.',
        ]);

        try {
            $ftpUser = FtpUser::findOrFail($this->selectedUserId);
            $ftpService->updateQuota($ftpUser, $this->quota_mb);

            $this->showQuotaModal = false;

            $this->dispatch('ftpUserUpdated');
            session()->flash('success', 'Quota updated successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update quota: '.$e->getMessage());
        }
    }

    public function toggleStatus($userId, FtpService $ftpService)
    {
        try {
            $ftpUser = FtpUser::findOrFail($userId);

            if ($ftpUser->is_active) {
                $ftpService->disableUser($ftpUser);
                session()->flash('success', 'FTP user disabled successfully!');
            } else {
                $ftpService->enableUser($ftpUser);
                session()->flash('success', 'FTP user enabled successfully!');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to toggle user status: '.$e->getMessage());
        }
    }

    public function unlockUser($userId, FtpService $ftpService)
    {
        try {
            $ftpUser = FtpUser::findOrFail($userId);
            $ftpService->unlockUser($ftpUser);

            session()->flash('success', 'FTP user unlocked successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to unlock user: '.$e->getMessage());
        }
    }

    public function updateDiskUsage($userId, FtpService $ftpService)
    {
        try {
            $ftpUser = FtpUser::findOrFail($userId);
            $ftpService->updateDiskUsage($ftpUser);

            session()->flash('success', 'Disk usage update queued. This may take a few moments.');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update disk usage: '.$e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->selectedUserId = null;
        $this->username = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->home_directory = "/var/www/vhosts/{$this->site->domain}/httpdocs";
        $this->quota_mb = 1024;
        $this->max_connections = 5;
        $this->bandwidth_limit_kbps = null;
        $this->require_ssl = true;
        $this->allowed_ip = null;
        $this->notes = '';
        $this->is_active = true;

        $this->resetErrorBag();
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showPasswordModal = false;
        $this->showQuotaModal = false;
        $this->showConnectionInfoModal = false;
        $this->resetForm();
    }
}

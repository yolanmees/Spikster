<?php

namespace App\Livewire\Settings;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    private const SUPER_ADMIN_ROLE = 'Super Admin';

    public $search = '';

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $userId;

    public $name;

    public $email;

    public $password;

    public $password_confirmation;

    public $selectedRoles = [];

    protected $queryString = ['search'];

    private function ensureCan(string $permission): void
    {
        abort_unless(Auth::user()?->can($permission), 403);
    }

    private function isSuperAdmin(?User $user = null): bool
    {
        return (bool) ($user && method_exists($user, 'hasRole') && $user->hasRole(self::SUPER_ADMIN_ROLE));
    }

    public function render()
    {
        $this->ensureCan('user.view');

        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->with('roles')
            ->paginate(10);

        $roles = Role::all();

        return view('livewire.settings.user-management', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function openCreateModal()
    {
        $this->ensureCan('user.create');

        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($userId)
    {
        $this->ensureCan('user.edit');

        $user = User::findOrFail($userId);

        if ($this->isSuperAdmin($user) && ! $this->isSuperAdmin(Auth::user())) {
            abort(403);
        }

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->showEditModal = true;
    }

    public function openDeleteModal($userId)
    {
        $this->ensureCan('user.delete');

        $user = User::findOrFail($userId);
        if ($this->isSuperAdmin($user) && ! $this->isSuperAdmin(Auth::user())) {
            abort(403);
        }

        $this->userId = $userId;
        $this->showDeleteModal = true;
    }

    public function createUser()
    {
        $this->ensureCan('user.create');

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'selectedRoles' => 'array',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        if (! empty($this->selectedRoles)) {
            $roles = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();

            if (in_array(self::SUPER_ADMIN_ROLE, $roles, true) && ! $this->isSuperAdmin(Auth::user())) {
                abort(403);
            }

            $user->syncRoles($roles);
        }

        AuditService::logCreate($user, "User created: {$user->email}");
        session()->flash('success', 'User created successfully!');
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function updateUser()
    {
        $this->ensureCan('user.edit');

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->userId,
            'password' => 'nullable|min:8|confirmed',
            'selectedRoles' => 'array',
        ]);

        $user = User::findOrFail($this->userId);
        $actor = Auth::user();

        if ($this->isSuperAdmin($user) && ! $this->isSuperAdmin($actor)) {
            abort(403);
        }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password ? bcrypt($this->password) : $user->password,
        ]);

        $roles = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();

        if (in_array(self::SUPER_ADMIN_ROLE, $roles, true) && ! $this->isSuperAdmin($actor)) {
            abort(403);
        }

        // A super admin cannot remove their own super-admin role from the UI.
        if ($actor && $actor->id === $user->id
            && $this->isSuperAdmin($actor)
            && ! in_array(self::SUPER_ADMIN_ROLE, $roles, true)) {
            session()->flash('error', 'You cannot remove your own Super Admin role.');

            return;
        }

        $user->syncRoles($roles);

        AuditService::logUpdate($user, ['name' => $user->getOriginal('name'), 'email' => $user->getOriginal('email')], "User updated: {$user->email}");
        session()->flash('success', 'User updated successfully!');
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function deleteUser()
    {
        $this->ensureCan('user.delete');

        $user = User::findOrFail($this->userId);

        if ($this->isSuperAdmin($user) && ! $this->isSuperAdmin(Auth::user())) {
            abort(403);
        }

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot delete your own account!');
            $this->showDeleteModal = false;

            return;
        }

        AuditService::logDelete($user, "User deleted: {$user->email}");
        $user->delete();

        session()->flash('success', 'User deleted successfully!');
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}

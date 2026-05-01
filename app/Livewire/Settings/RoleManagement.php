<?php

namespace App\Livewire\Settings;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagement extends Component
{
    use WithPagination;

    private const SUPER_ADMIN_ROLE = 'Super Admin';

    private const PROTECTED_ROLES = ['Super Admin', 'Admin', 'Reseller', 'Customer', 'User'];

    public $search = '';

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $roleId;

    public $roleName;

    public $selectedPermissions = [];

    protected $queryString = ['search'];

    private function ensureCan(string $permission): void
    {
        abort_unless(Auth::user()?->can($permission), 403);
    }

    private function isSuperAdmin(): bool
    {
        $user = Auth::user();

        return (bool) ($user && method_exists($user, 'hasRole') && $user->hasRole(self::SUPER_ADMIN_ROLE));
    }

    public function render()
    {
        $this->ensureCan('role.view');

        $roles = Role::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->get()
            ->map(function ($role) {
                $role->users_count = $role->users()->count();

                return $role;
            });

        // Manually paginate for better control
        $currentPage = request()->get('page', 1);
        $perPage = 10;
        $paginatedRoles = new LengthAwarePaginator(
            $roles->forPage($currentPage, $perPage),
            $roles->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);

            return $parts[0] ?? 'other';
        });

        return view('livewire.settings.role-management', [
            'roles' => $paginatedRoles,
            'permissions' => $permissions,
        ]);
    }

    public function openCreateModal()
    {
        $this->ensureCan('role.create');

        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($roleId)
    {
        $this->ensureCan('role.edit');

        $role = Role::findOrFail($roleId);
        if (in_array($role->name, self::PROTECTED_ROLES, true) && ! $this->isSuperAdmin()) {
            abort(403);
        }

        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->showEditModal = true;
    }

    public function openDeleteModal($roleId)
    {
        $this->ensureCan('role.delete');

        $role = Role::findOrFail($roleId);
        if (in_array($role->name, self::PROTECTED_ROLES, true) && ! $this->isSuperAdmin()) {
            abort(403);
        }

        $this->roleId = $roleId;
        $this->showDeleteModal = true;
    }

    public function createRole()
    {
        $this->ensureCan('role.create');
        $this->ensureCan('permission.assign');

        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::create(['name' => $this->roleName]);

        if (! empty($this->selectedPermissions)) {
            $role->syncPermissions($this->selectedPermissions);
        }

        session()->flash('success', 'Role created successfully!');
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function updateRole()
    {
        $this->ensureCan('role.edit');
        $this->ensureCan('permission.assign');

        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,'.$this->roleId,
            'selectedPermissions' => 'array',
        ]);

        $role = Role::findOrFail($this->roleId);

        if (in_array($role->name, self::PROTECTED_ROLES, true) && ! $this->isSuperAdmin()) {
            abort(403);
        }

        $role->update(['name' => $this->roleName]);
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role updated successfully!');
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function deleteRole()
    {
        $this->ensureCan('role.delete');

        $role = Role::findOrFail($this->roleId);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            session()->flash('error', 'Core roles cannot be deleted.');
            $this->showDeleteModal = false;

            return;
        }

        // Prevent deleting if role has users
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Cannot delete role that has users assigned!');
            $this->showDeleteModal = false;

            return;
        }

        $role->delete();

        session()->flash('success', 'Role deleted successfully!');
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->roleId = null;
        $this->roleName = '';
        $this->selectedPermissions = [];
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

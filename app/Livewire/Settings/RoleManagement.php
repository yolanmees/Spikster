<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    public $roleId;
    public $roleName;
    public $selectedPermissions = [];

    protected $queryString = ['search'];

    public function render()
    {
        $roles = Role::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get()
            ->map(function($role) {
                $role->users_count = $role->users()->count();
                return $role;
            });

        // Manually paginate for better control
        $currentPage = request()->get('page', 1);
        $perPage = 10;
        $paginatedRoles = new \Illuminate\Pagination\LengthAwarePaginator(
            $roles->forPage($currentPage, $perPage),
            $roles->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $permissions = Permission::all()->groupBy(function($permission) {
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
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($roleId)
    {
        $role = Role::findOrFail($roleId);
        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->showEditModal = true;
    }

    public function openDeleteModal($roleId)
    {
        $this->roleId = $roleId;
        $this->showDeleteModal = true;
    }

    public function createRole()
    {
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::create(['name' => $this->roleName]);

        if (!empty($this->selectedPermissions)) {
            $role->syncPermissions($this->selectedPermissions);
        }

        session()->flash('success', 'Role created successfully!');
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function updateRole()
    {
        $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . $this->roleId,
            'selectedPermissions' => 'array',
        ]);

        $role = Role::findOrFail($this->roleId);
        $role->update(['name' => $this->roleName]);
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role updated successfully!');
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function deleteRole()
    {
        $role = Role::findOrFail($this->roleId);

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

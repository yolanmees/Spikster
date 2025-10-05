<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

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

    public function render()
    {
        $users = User::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
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
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($userId)
    {
        $user = User::findOrFail($userId);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->showEditModal = true;
    }

    public function openDeleteModal($userId)
    {
        $this->userId = $userId;
        $this->showDeleteModal = true;
    }

    public function createUser()
    {
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

        if (!empty($this->selectedRoles)) {
            // Get role names from IDs
            $roles = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();
            $user->syncRoles($roles);
        }

        session()->flash('success', 'User created successfully!');
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function updateUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => 'nullable|min:8|confirmed',
            'selectedRoles' => 'array',
        ]);

        $user = User::findOrFail($this->userId);
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password ? bcrypt($this->password) : $user->password,
        ]);

        // Get role names from IDs
        $roles = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();
        $user->syncRoles($roles);

        session()->flash('success', 'User updated successfully!');
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function deleteUser()
    {
        $user = User::findOrFail($this->userId);

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot delete your own account!');
            $this->showDeleteModal = false;
            return;
        }

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

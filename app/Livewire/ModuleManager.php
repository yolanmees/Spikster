<?php

namespace App\Livewire;

use App\Models\Module;
use App\Services\ModuleRegistry;
use Livewire\Component;
use Livewire\WithPagination;

class ModuleManager extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $statusFilter = 'all'; // all, active, inactive

    public $showingModule = null;
    public $moduleDetails = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        //
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function getModulesProperty()
    {
        $query = Module::query()
            ->with(['dependencies', 'menuItems', 'widgets', 'hooks'])
            ->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('alias', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->category) {
            $query->byCategory($this->category);
        }

        if ($this->statusFilter === 'active') {
            $query->active();
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate(12);
    }

    public function getCategoriesProperty()
    {
        return Module::query()
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->sort();
    }

    public function showDetails($moduleId)
    {
        $this->showingModule = $moduleId;
        $this->moduleDetails = Module::with([
            'settings',
            'dependencies.requiredModule',
            'dependents.module',
            'menuItems.children',
            'widgets',
            'hooks',
            'permissions.permission',
            'installedBy',
        ])->find($moduleId);
    }

    public function closeDetails()
    {
        $this->showingModule = null;
        $this->moduleDetails = null;
    }

    public function toggleModule($moduleId)
    {
        $module = Module::find($moduleId);

        if (!$module) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Module not found'
            ]);
            return;
        }

        try {
            $registry = app(ModuleRegistry::class);

            if ($module->is_active) {
                $registry->disable($module);
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => "{$module->name} disabled successfully"
                ]);
            } else {
                $registry->enable($module);
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => "{$module->name} enabled successfully"
                ]);
            }

            // Refresh module details if showing
            if ($this->showingModule == $moduleId) {
                $this->showDetails($moduleId);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function checkHealth($moduleId)
    {
        $module = Module::find($moduleId);

        if (!$module) {
            return;
        }

        try {
            $registry = app(ModuleRegistry::class);
            $health = $registry->checkHealth($module);

            $this->dispatch('notify', [
                'type' => $health['status'] === 'healthy' ? 'success' : 'warning',
                'message' => "Health check complete: {$health['status']}"
            ]);

            // Refresh module details if showing
            if ($this->showingModule == $moduleId) {
                $this->showDetails($moduleId);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Health check failed: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.module-manager', [
            'modules' => $this->modules,
            'categories' => $this->categories,
        ]);
    }
}


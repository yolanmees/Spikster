<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\ModuleRegistry;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function __construct(
        private ModuleRegistry $moduleRegistry
    ) {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display a listing of the modules.
     */
    public function index()
    {
        // Use the Livewire component for the module manager
        return view('modules.manager');
    }

    /**
     * Display the specified module.
     */
    public function show(string $id)
    {
        $module = Module::with(['menuItems', 'permissions'])->findOrFail($id);

        return view('modules.show', [
            'module' => $module,
        ]);
    }

    /**
     * Enable a module.
     */
    public function enable(string $id)
    {
        $module = Module::findOrFail($id);

        try {
            $this->moduleRegistry->enable($module);

            return redirect()
                ->route('modules.index')
                ->with('success', "Module '{$module->name}' has been enabled successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->route('modules.index')
                ->with('error', "Failed to enable module: {$e->getMessage()}");
        }
    }

    /**
     * Disable a module.
     */
    public function disable(string $id)
    {
        $module = Module::findOrFail($id);

        try {
            $this->moduleRegistry->disable($module);

            return redirect()
                ->route('modules.index')
                ->with('success', "Module '{$module->name}' has been disabled successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->route('modules.index')
                ->with('error', "Failed to disable module: {$e->getMessage()}");
        }
    }

    /**
     * Toggle module status (enable/disable).
     */
    public function toggle(string $id)
    {
        $module = Module::findOrFail($id);

        if ($module->is_active) {
            return $this->disable($id);
        }

        return $this->enable($id);
    }
}

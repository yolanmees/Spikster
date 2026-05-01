<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ModuleRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleRegistry $registry
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Module::query()
            ->with(['settings', 'dependencies', 'menuItems', 'widgets', 'hooks', 'permissions'])
            ->ordered();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->boolean('installed_only')) {
            $query->installed();
        }

        $modules = $query->get();

        return response()->json([
            'success' => true,
            'modules' => $modules->map(fn ($module) => [
                'id' => $module->id,
                'name' => $module->name,
                'alias' => $module->alias,
                'description' => $module->description,
                'version' => $module->version,
                'author' => $module->author,
                'category' => $module->category,
                'icon' => $module->icon,
                'color' => $module->color,
                'priority' => $module->priority,
                'is_active' => $module->is_active,
                'is_core' => $module->is_core,
                'is_installed' => $module->is_installed,
                'can_disable' => $module->canBeDisabled(),
                'health' => [
                    'status' => $module->health_status,
                    'data' => $module->health_data,
                    'last_checked' => $module->last_checked_at?->diffForHumans(),
                ],
                'installed_at' => $module->installed_at?->format('Y-m-d H:i:s'),
                'installed_by' => $module->installedBy?->name,
                'stats' => [
                    'settings_count' => $module->settings->count(),
                    'dependencies_count' => $module->dependencies->count(),
                    'menu_items_count' => $module->menuItems->count(),
                    'widgets_count' => $module->widgets->count(),
                    'hooks_count' => $module->hooks->count(),
                    'permissions_count' => $module->permissions->count(),
                ],
            ]),
        ]);
    }

    public function show(Module $module): JsonResponse
    {
        $module->load([
            'settings',
            'dependencies.requiredModule',
            'dependents.module',
            'menuItems.children',
            'widgets',
            'hooks',
            'permissions.permission',
            'installedBy',
        ]);

        return response()->json([
            'success' => true,
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'alias' => $module->alias,
                'description' => $module->description,
                'version' => $module->version,
                'author' => $module->author,
                'category' => $module->category,
                'icon' => $module->icon,
                'color' => $module->color,
                'priority' => $module->priority,
                'is_active' => $module->is_active,
                'is_core' => $module->is_core,
                'is_installed' => $module->is_installed,
                'can_disable' => $module->canBeDisabled(),
                'metadata' => $module->metadata,
                'health' => [
                    'status' => $module->health_status,
                    'data' => $module->health_data,
                    'last_checked' => $module->last_checked_at,
                ],
                'installed_at' => $module->installed_at,
                'installed_by' => $module->installedBy?->only(['id', 'name', 'email']),
                'settings' => $module->settings,
                'dependencies' => $module->dependencies,
                'dependents' => $module->dependents,
                'menu_items' => $module->menuItems,
                'widgets' => $module->widgets,
                'hooks' => $module->hooks,
                'permissions' => $module->permissions,
            ],
        ]);
    }

    public function discover(): JsonResponse
    {
        try {
            $discovered = $this->registry->discover();

            return response()->json([
                'success' => true,
                'discovered' => $discovered,
                'message' => 'Found '.count($discovered).' module(s)',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to discover modules: '.$e->getMessage(),
            ], 500);
        }
    }

    public function install(Request $request): JsonResponse
    {
        $request->validate([
            'alias' => 'required|string',
        ]);

        try {
            $module = $this->registry->register($request->alias);

            return response()->json([
                'success' => true,
                'module' => $module,
                'message' => "Module {$module->name} installed successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to install module: '.$e->getMessage(),
            ], 500);
        }
    }

    public function enable(Module $module): JsonResponse
    {
        try {
            $this->registry->enable($module);

            return response()->json([
                'success' => true,
                'module' => $module->fresh(),
                'message' => "Module {$module->name} enabled successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enable module: '.$e->getMessage(),
            ], 400);
        }
    }

    public function disable(Module $module): JsonResponse
    {
        try {
            $this->registry->disable($module);

            return response()->json([
                'success' => true,
                'module' => $module->fresh(),
                'message' => "Module {$module->name} disabled successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable module: '.$e->getMessage(),
            ], 400);
        }
    }

    public function checkHealth(Module $module): JsonResponse
    {
        try {
            $health = $this->registry->checkHealth($module);

            return response()->json([
                'success' => true,
                'health' => $health,
                'module' => $module->fresh()->only(['health_status', 'health_data', 'last_checked_at']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check module health: '.$e->getMessage(),
            ], 500);
        }
    }

    public function checkDependencies(Module $module): JsonResponse
    {
        try {
            $unsatisfied = $this->registry->checkDependencies($module);

            return response()->json([
                'success' => true,
                'satisfied' => empty($unsatisfied),
                'unsatisfied' => $unsatisfied,
                'dependencies' => $module->dependencies()->with('requiredModule')->get(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check dependencies: '.$e->getMessage(),
            ], 500);
        }
    }
}

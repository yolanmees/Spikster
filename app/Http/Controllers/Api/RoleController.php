<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermissionAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * List all roles.
     */
    public function index(Request $request)
    {
        $this->authorize('role.view');

        try {
            $roles = Role::with('permissions')
                ->withCount('users')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new role.
     */
    public function store(Request $request)
    {
        $this->authorize('role.create');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $role = Role::create(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            // Log the event
            PermissionAuditLog::logEvent(
                $request->user()->id,
                'role_created',
                null,
                $role->id,
                null,
                ['role_name' => $role->name, 'permissions_count' => $role->permissions->count()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role->load('permissions'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show role details.
     */
    public function show(Request $request, $id)
    {
        $this->authorize('role.view');

        try {
            $role = Role::with('permissions')
                ->withCount('users')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
            ], 404);
        }
    }

    /**
     * Update role.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('role.edit');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,'.$id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $role = Role::findOrFail($id);
            $oldName = $role->name;

            $role->update(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            // Log the event
            PermissionAuditLog::logEvent(
                $request->user()->id,
                'role_updated',
                null,
                $role->id,
                null,
                [
                    'old_name' => $oldName,
                    'new_name' => $role->name,
                    'permissions_count' => $role->permissions->count(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role->load('permissions'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete role.
     */
    public function destroy(Request $request, $id)
    {
        $this->authorize('role.delete');

        try {
            $role = Role::findOrFail($id);

            // Prevent deletion of core roles
            if (in_array($role->name, ['Super Admin', 'Admin', 'Reseller', 'User'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete core system roles',
                ], 403);
            }

            $roleName = $role->name;
            $role->delete();

            // Log the event
            PermissionAuditLog::logEvent(
                $request->user()->id,
                'role_deleted',
                null,
                null,
                null,
                ['role_name' => $roleName]
            );

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get role permissions.
     */
    public function getPermissions($id)
    {
        $this->authorize('role.view');

        try {
            $role = Role::with('permissions')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $role->permissions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
            ], 404);
        }
    }

    /**
     * Assign permissions to role.
     */
    public function assignPermissions(Request $request, $id)
    {
        $this->authorize('permission.assign');

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $role = Role::findOrFail($id);
            $role->syncPermissions($request->permissions);

            // Log the event
            PermissionAuditLog::logEvent(
                $request->user()->id,
                'role_permissions_updated',
                null,
                $role->id,
                null,
                [
                    'role_name' => $role->name,
                    'permissions_count' => count($request->permissions),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned successfully',
                'data' => $role->load('permissions'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

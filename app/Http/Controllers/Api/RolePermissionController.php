<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    use ApiResponse;

    public function roles()
    {
        $roles = Role::with('permissions')->latest()->get();

        return $this->successResponse($roles);
    }

    public function showRole($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return $this->successResponse($role);
    }

    public function permissions()
    {
        $permissions = Permission::latest()->get();

        return $this->successResponse($permissions);
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'api']);

        if (!empty($validated['permissions'])) {
            $role->givePermissionTo($validated['permissions']);
        }

        return $this->successResponse($role->load('permissions'), 'Role created successfully', 201);
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if ($request->has('permissions')) {
            $role->syncPermissions($validated['permissions']);
        }

        return $this->successResponse($role->fresh()->load('permissions'), 'Role updated successfully');
    }

    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['admin', 'avocato', 'client'])) {
            return $this->errorResponse('Cannot delete system roles', 403);
        }

        $role->delete();

        return $this->successResponse(null, 'Role deleted successfully');
    }
}

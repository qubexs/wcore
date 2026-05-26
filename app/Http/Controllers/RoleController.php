<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use App\Models\Menu;
use App\Services\ActivityLogService;  // ← Added this
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions
            ->reject(fn($p) => str_starts_with($p->name, '_ignition') || str_contains($p->name, 'ignition'))
            ->groupBy('module');

        return view('roles.create', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync permissions if provided
        $permissionIds = $validated['permissions'] ?? [];
        if (!empty($permissionIds)) {
            $role->permissions()->sync($permissionIds);
        }

        // ✅ LOG ROLE CREATION
        ActivityLogService::roleCreated($role, $permissionIds);
        // Or use helper:
        // logRoleCreated($role, $permissionIds);

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' created successfully with " . count($permissionIds) . " permissions.");
    }

    /**
     * Show the form for editing the specified role.
     * Manages both Layer 1 (Permissions) and Layer 2 (Department Menu Access)
     */
    public function edit(Role $role)
    {
        // Layer 1: Spatie Permissions
        $allPermissions = Permission::orderBy('name')->get();
        $rolePermissionIds = $role->permissions()->pluck('permissions.id')->toArray();
        $groupedPermissions = $allPermissions
            ->reject(fn($p) => str_starts_with($p->name, '_ignition') || str_contains($p->name, 'ignition'))
            ->groupBy('module');

        // Layer 2: Department Menu Access
        $departments = Department::orderBy('name')->get();
        $menus = Menu::orderBy('title')->get();
        
        // Get assigned department-menu combinations for this role
        // (We track which departments this role's users can access menus from)
        $roleDepartments = $role->departments ?? collect();

        return view('roles.edit', compact(
            'role',
            'allPermissions',
            'groupedPermissions',
            'rolePermissionIds',
            'departments',
            'menus',
            'roleDepartments'
        ));
    }

    /**
     * Update the specified role in storage.
     * Updates both permissions and department associations
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
            'departments' => 'array',
            'departments.*' => 'integer|exists:departments,id',
        ]);

        // Update role info
        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Layer 1: Sync permissions
        $permissionIds = $validated['permissions'] ?? [];
        $role->permissions()->sync($permissionIds);

        // Layer 2: Sync department access
        if (method_exists($role, 'departments')) {
            $departmentIds = $validated['departments'] ?? [];
            $role->departments()->sync($departmentIds);
        }

        // ✅ LOG ROLE UPDATE
        ActivityLogService::roleUpdated($role, $permissionIds);
        // Or use helper:
        // logRoleUpdated($role, $permissionIds);

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' updated successfully with " . count($permissionIds) . " permissions.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting if users are assigned
        if ($role->users()->exists()) {
            return back()->with('error', "Cannot delete role '{$role->name}' - users are assigned to it.");
        }

        $name = $role->name;

        // ✅ LOG ROLE DELETION (before deleting)
        ActivityLogService::roleDeleted($name);
        // Or use helper:
        // logRoleDeleted($name);

        // Clean up relationships
        $role->permissions()->detach();
        if (method_exists($role, 'departments')) {
            $role->departments()->detach();
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Role '{$name}' deleted successfully.");
    }
}

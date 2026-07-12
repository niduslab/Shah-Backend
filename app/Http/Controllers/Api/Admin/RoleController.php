<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * List all roles with their permissions and how many users hold each.
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => $this->transform($role));

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * List all permissions grouped by module, for building the role editor UI.
     */
    public function permissions(): JsonResponse
    {
        $grouped = Permission::orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0])
            ->map(fn ($permissions) => $permissions->pluck('name')->values());

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Get a single role.
     */
    public function show(Role $role): JsonResponse
    {
        $role->loadCount('users');

        return response()->json([
            'success' => true,
            'data' => $this->transform($role),
        ]);
    }

    /**
     * Create a new custom role.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => $this->transform($role->fresh('permissions')),
        ], 201);
    }

    /**
     * Update a role's name and/or permissions.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $this->transform($role->fresh('permissions')),
        ]);
    }

    /**
     * Delete a role, as long as no user currently holds it.
     */
    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This role is assigned to one or more users and cannot be deleted. Reassign those users first.',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }

    protected function transform(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->values(),
            'users_count' => $role->users_count ?? $role->users()->count(),
        ];
    }
}

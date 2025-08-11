<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    /**
     * Get all roles with users count
     */
    public function index()
    {
        $roles = Role::all();
        
        // Her role için user count'u manuel olarak hesapla
        $rolesWithCount = $roles->map(function ($role) {
            $role->users_count = $role->users()->count();
            return $role;
        });

        return response()->json([
            'roles' => $rolesWithCount,
            'totalRoles' => $roles->count(),
        ]);
    }

    /**
     * Get users by role
     */
    public function users(Request $request)
    {
        $role = $request->get('role', 'all');
        $page = $request->get('page', 1);
        $itemsPerPage = $request->get('itemsPerPage', 10);
        $searchQuery = $request->get('searchQuery', '');
        $sortBy = $request->get('sortBy', 'name');
        $orderBy = $request->get('orderBy', 'asc');

        $query = User::with('roles', 'permissions');

        // Filter by role
        if ($role !== 'all') {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Search
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        }

        // Sort
        $query->orderBy($sortBy, $orderBy);

        // Paginate
        $users = $query->paginate($itemsPerPage, ['*'], 'page', $page);

        return response()->json([
            'users' => $users->items(),
            'totalUsers' => $users->total(),
            'currentPage' => $users->currentPage(),
            'lastPage' => $users->lastPage(),
            'perPage' => $users->perPage(),
        ]);
    }

    /**
     * Get all available roles for dropdown
     */
    public function availableRoles()
    {
        $roles = Role::select('id', 'name')->get();
        
        return response()->json([
            'roles' => $roles,
        ]);
    }

    /**
     * Get role details with permissions
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        
        return response()->json([
            'role' => $role,
        ]);
    }

    /**
     * Create new role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role,
        ], 201);
    }

    /**
     * Update role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role,
        ]);
    }

    /**
     * Delete role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * List users with pagination, search, optional role filter, and sorting.
     */
    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $itemsPerPage = (int) $request->get('itemsPerPage', 10);
        $search = trim((string) $request->get('search', $request->get('searchQuery', '')));
        $role = $request->get('role');
        $sortBy = $request->get('sortBy', 'name');
        $orderBy = $request->get('orderBy', 'asc');

        $query = User::query()->with(['roles:id,name', 'permissions:id,name']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role && $role !== 'all') {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Guard: allow only sortable fields
        $sortable = ['name', 'email', 'created_at'];
        if (!in_array($sortBy, $sortable, true)) {
            $sortBy = 'name';
        }
        $orderBy = strtolower($orderBy) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $orderBy);

        $users = $query->paginate($itemsPerPage > 0 ? $itemsPerPage : 10, ['*'], 'page', $page);

        return response()->json([
            'users' => $users->items(),
            'totalUsers' => $users->total(),
            'currentPage' => $users->currentPage(),
            'lastPage' => $users->lastPage(),
            'perPage' => $users->perPage(),
        ]);
    }

    /**
     * Show single user with roles & permissions
     */
    public function show(int $id)
    {
        $user = User::with(['roles:id,name', 'permissions:id,name'])->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Create a new user and optionally assign roles/permissions
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->save();

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }
        if (!empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        $user->load(['roles:id,name', 'permissions:id,name']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Update user details and optionally roles/permissions
     */
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles'] ?? []);
        }
        if (array_key_exists('permissions', $validated)) {
            $user->syncPermissions($validated['permissions'] ?? []);
        }

        $user->load(['roles:id,name', 'permissions:id,name']);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Delete user
     */
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}

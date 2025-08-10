<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Create user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string',
            'c_password' => 'required|same:password'
        ]);

        $user = new \App\Models\User([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($user->save()) {
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'message' => 'Successfully created user!',
                'accessToken' => $token,
            ], 201);
        }

        return response()->json(['error' => 'Provide proper details']);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Create token using Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        // Build abilities from model helper
        try {
            $userAbilityRules = method_exists($user, 'getAbilityRules')
                ? $user->getAbilityRules()
                : [];
        } catch (\Exception $e) {
            $userAbilityRules = [];
        }

        return response()->json([
            'accessToken' => $token,
            'userData' => $user,
            'userAbilityRules' => $userAbilityRules,
        ]);
    }

    /**
     * Get the authenticated User
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get all permissions
     */
    public function permissions()
    {
        $permissions = Permission::all(['id', 'name', 'guard_name', 'created_at', 'updated_at']);
        return response()->json($permissions);
    }

    /**
     * Send a password reset link to the given email.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
                'status' => $status,
            ]);
        }

        return response()->json([
            'message' => __($status),
            'status' => $status,
        ], 400);
    }

    /**
     * Handle an incoming new password request after clicking email link.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
                'status' => $status,
            ]);
        }

        return response()->json([
            'message' => __($status),
            'status' => $status,
        ], 400);
    }

    /**
     * DEBUG: Inspect a user's roles/permissions and computed ability rules.
     * Not for production use. Optionally pass ?id= or /auth/debug/{id}.
     */
    public function debugUser(Request $request, int $id = null)
    {
        // Optionally restrict to local
        // if (!config('app.debug')) { abort(403); }

        /** @var \App\Models\User|null $user */
        $user = null;
        if ($id) {
            $user = \App\Models\User::find($id);
        }
        if (!$user) {
            $user = $request->user();
        }
        if (!$user) {
            return response()->json(['error' => 'No user'], 404);
        }

        // Spatie helpers
        $helperRoles = $user->getRoleNames();
        $helperPerms = $user->getAllPermissions()->pluck('name');

        // Direct relations (ignore guard filters)
        $relRoles = $user->roles()->pluck('name');
        $relPermsDirect = $user->permissions()->pluck('name');

        // Role-based permissions
        $rolePermIds = DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
            ->join(config('permission.table_names.model_has_roles', 'model_has_roles'), 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $user->getKey())
            ->where('model_has_roles.model_type', $user->getMorphClass())
            ->pluck('permission_id');
        $relPermsViaRoles = \Spatie\Permission\Models\Permission::whereIn('id', $rolePermIds)->pluck('name');

        // Computed ability rules
        $abilityRules = [];
        try {
            $abilityRules = $user->getAbilityRules();
        } catch (\Throwable $e) {
            $abilityRules = ['error' => $e->getMessage()];
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'guard_name' => property_exists($user, 'guard_name') ? $user->guard_name : null,
            ],
            'helper_roles' => $helperRoles,
            'helper_permissions' => $helperPerms,
            'rel_roles' => $relRoles,
            'rel_permissions_direct' => $relPermsDirect,
            'rel_permissions_via_roles' => $relPermsViaRoles,
            'ability_rules' => $abilityRules,
        ]);
    }
}

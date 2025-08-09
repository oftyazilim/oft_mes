<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Validator;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string',
            'c_password' => 'required|same:password'
        ]);

        $user = new User([
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
        } else {
            return response()->json(['error' => 'Provide proper details']);
        }
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

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Create token using Sanctum
            $token = $user->createToken('auth-token')->plainTextToken;

            // Spatie permissions - hata kontrolü ile
            try {
                $permissions = $user->getAllPermissions()->pluck('name');
                $roles = $user->getRoleNames();

                $userAbilityRules = [
                    'action' => $permissions->toArray(),
                    'subject' => $roles->toArray(),
                ];
            } catch (\Exception $e) {
                // Spatie permission hatası varsa boş array döndür
                $userAbilityRules = [];
            }

            return response()->json([
                'accessToken' => $token,
                'userData' => $user,
                'userAbilityRules' => $userAbilityRules,
            ]);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }






    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
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
     *
     * @return [json] permissions collection
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

        // Attempt to send the password reset link to the user's email.
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
}

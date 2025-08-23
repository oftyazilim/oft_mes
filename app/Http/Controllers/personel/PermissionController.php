<?php

namespace App\Http\Controllers\personel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Tüm izinleri listele
     */
    public function index()
    {
        $perms = Permission::select('id', 'name', 'guard_name', 'created_at', 'updated_at')->get();
        return response()->json($perms);
    }

    /**
     * Yeni izin oluştur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $perm = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return response()->json([
            'message' => 'İzin başarıyla oluşturuldu',
            'permission' => $perm,
        ], 201);
    }

    /**
     * İzni sil
     */
    public function destroy(int $id)
    {
        $perm = Permission::find($id);
        if (!$perm) {
            return response()->json(['message' => 'İzin bulunamadı'], 404);
        }
        $perm->delete();
        return response()->json(['message' => 'İzin silindi']);
    }
    // PermissionController.php
    public function getPermissions($id)
    {
        // Log::info($id);
        $user = User::find($id); // Kullanıcıyı bul
        $assigned = $user->permissions; // Atanmış izinler
        $available = Permission::whereNotIn('id', $assigned->pluck('id'))->get(); // Atanmamış izinler

        return response()->json([
            'assigned' => $assigned,
            'available' => $available,
        ]);
    }

    public function assignPermission($id, $permission)
    {
        // Log::info($permission);
        $user = User::find($id);
        // $user->syncPermissions(['delete', 'delete_articles']);
        // $user->save();
        $izin = Permission::findOrFail($permission); // İzin al
        // Log::info($izin);

        // DB::beginTransaction();

        // $permission = Permission::create(['name' => 'read_dashboard']);
        // try {
        //$izin = 'manage';
        $user->givePermissionTo($izin);
        //  $user->save();
        // DB::commit();
        // } catch (\Exception $e) {
        //     // DB::rollBack();
        //     Log::error('Error assigning permission: ' . $e->getMessage());
        // }
        // $user->givePermissionTo('read');
        // $user->save();
        return response()->noContent();
    }

    public function removePermission($id, Permission $permission)
    {
        $user = User::find($id); // Kullanıcıyı bul

        $user->revokePermissionTo($permission->name);

        return response()->noContent();
    }
}

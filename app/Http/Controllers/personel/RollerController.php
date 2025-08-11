<?php

namespace App\Http\Controllers\personel;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class RollerController extends Controller
{
    public function index()
    {
        // Sadece name kolonunu döndür
        $roles = Role::all();

        // Gelen veriyi JSON formatında döndür
        return response()->json($roles);
    }

    public function getPermissions(Role $role)
    {
        $assigned = $role->permissions; // Role'e atanmış izinler
        $available = Permission::whereNotIn('id', $assigned->pluck('id'))->get(); // Atanmamış izinler

        return response()->json([
            'assigned' => $assigned,
            'available' => $available,
        ]);
    }

    public function getRolesWithUserCount()
    {
        $roles = Role::withCount('users')->get();
        // Log::info($roles);
        return response()->json($roles);
    }

    public function getUsersWithRoles($rol)
    {
        // Log::info($rol);
        // $role = Role::where('name', $rol)->first();
        // $users = $role->users;
        $users = User::role($rol)->get();
        //  Log::info($users);
        return response()->json($users);
    }

    public function assignPermission(Role $role, Request $request)
    {

        $request->validate(['permission_id' => 'required|exists:permissions,id']);
        $permission = Permission::findOrFail($request->permission_id);

        $role->givePermissionTo($permission->name);

        return response()->noContent();
    }

    public function removePermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission->name);

        return response()->noContent();
    }










    public function user_role($userId)
    {
        // Kullanıcıyı ID'ye göre bul
        $user = User::find($userId);

        // Kullanıcının rollerini al
        if ($user) {
            $roles = $user->getRoleNames(); // Kullanıcının rollerini al

            // Roller üzerinde işlem yapabilir ya da sadece dönebilirsiniz
            return response()->json([
                'roles' => $roles,
            ]);
        }

        return response()->json([
            'message' => 'User not found',
        ], 404);
    }


    public function store(Request $request)
    {

        $role = Role::create(['name' => $request->name]);

        foreach ($request->permissions as $permission) {
            if ($permission['view']) {
                Permission::create([
                    'role_id' => $role->id,
                    'name' => $permission['name'],
                    'action' => 'view',
                ]);
            }
            if ($permission['add']) {
                Permission::create([
                    'role_id' => $role->id,
                    'name' => $permission['name'],
                    'action' => 'add',
                ]);
            }
            if ($permission['edit']) {
                Permission::create([
                    'role_id' => $role->id,
                    'name' => $permission['name'],
                    'action' => 'edit',
                ]);
            }
            if ($permission['delete']) {
                Permission::create([
                    'role_id' => $role->id,
                    'name' => $permission['name'],
                    'action' => 'delete',
                ]);
            }
        }

        return response()->json(['message' => 'Rol başarıyla oluşturuldu.']);
    }

    public function RolEkle(Request $request)
    {
        // Log::info('Hata burada mı acep');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        return response()->json(['message' => 'Rol başarıyla eklendi', 'role' => $role], 201);
    }

    public function RolGuncelle(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $id,
            ]);

            $role = Role::findOrFail($id);

            $role->update($validatedData);

            return response()->json(['message' => 'Rol başarıyla güncellendi', 'role' => $role], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Güncelleme sırasında hata oluştu', 'message' => $e->getMessage()], 500);
        }
    }

    public function RolSil($id)
    {
        try {
            $role = Role::findOrFail($id);

            $role->delete();

            return response()->json(['message' => 'Rol başarıyla silindi'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Silme işlemi sırasında hata oluştu', 'message' => $e->getMessage()], 500);
        }
    }



    // public function getRolesWithUserCount()
    // {
    //     // Her role için kullanıcı sayısını getiren sorgu
    //     $roles = Role::withCount(['users' => function ($query) {
    //         $query->whereNotNull('role_id'); // İlgili role bağlı kullanıcıları say
    //     }])->get();

    //     return response()->json($roles);
    // }
}

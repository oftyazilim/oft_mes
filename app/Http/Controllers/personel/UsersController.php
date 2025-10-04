<?php

namespace App\Http\Controllers\personel;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        // $users = User::get(['id', 'name', 'email']); // Sadece kullanıcı verilerini çek

        $data = DB::table('oftv_kullanicilar')
            ->get();

        // return response()->json(['users' => $users]);
        $user = User::pluck('name');
        $kademeler = User::select('tip')->distinct()->get();
        // Log::info($kademeler);
        return response()->json([
            'data' => $data,
            'user' => $user,
            'kademeler' => $kademeler,
        ]);
    }

    public function allBasic()
    {
        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'unvan' => 'required|string|max:255',
                // 'ismerkezi' => 'string|max:255',
                // 'istasyon' => 'string|max:255',
                'firma_id' => 'required|numeric',
                'firma_adi' => 'required|string|max:255',
                'proses' => 'string|max:255|nullable',
                'tip' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'roles' => 'required|array',
                'roles.*' => 'string|exists:roles,name',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'unvan' => $validated['unvan'],
                'co_id' => $validated['firma_id'],
                'co_name' => $validated['firma_adi'],
                'ismerkezi_id' => $request->ismerkezi === null ? '' : $request->ismerkezi,
                'istasyon_id' => $request->istasyon === null ? '' : $request->istasyon,
                'proses' => $validated['proses'],
                'tip' => $validated['tip'],
                'email_verified_at' => now(),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->syncRoles($validated['roles']);

            return response()->json(['message' => 'Kullanıcı başarıyla oluşturuldu.', 'user' => $user]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function getRoleUserCounts()
    {
        $rolesWithCounts = DB::table('users')
            ->select('role_id', DB::raw('COUNT(*) as user_count'))
            ->groupBy('role_id')
            ->get();


        return response()->json($rolesWithCounts);
    }

    public function show($id)
    {
        $data = User::find($id);

        if (!$data) {
            return response()->json(['message' => 'Kayıt bulunamadı'], 404);
        }

        return response()->json($data, 200);
    }

    public function update(Request $request, int $id)
    {
        Log::info($request->all());
        $user = User::find($id);
        // Log::info('User ID: ' . $user->id);
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'firma_id' => 'required|numeric',
                'firma_adi' => 'required|string|max:255',
                'unvan' => 'required|string|max:255',
                // 'ismerkezi' => 'string|max:255',
                // 'istasyon' => 'string|max:255',
                'proses' => 'nullable|string|max:255',
                'tip' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($user->id), // Mevcut kullanıcıyı hariç tut
                ],
                'password' => 'nullable|string|min:8', // Şifre boş bırakılabilir
                'roles' => 'required|array',
                'roles.*' => 'string|exists:roles,name',
            ]);

            // Kullanıcı bilgilerini güncelle
            $user->update([
                'name' => $validated['name'],
                'unvan' => $validated['unvan'],
                'co_id' => $validated['firma_id'],
                'co_name' => $validated['firma_adi'],
                'ismerkezi_id' => $request->ismerkezi === null ? '' : $request->ismerkezi,
                'istasyon_id' => $request->istasyon === null ? '' : $request->istasyon,
                'proses' => $validated['proses'],
                'tip' => $validated['tip'],
                'email' => $validated['email'],
            ]);

            // Rolleri güncelle
            $user->syncRoles($validated['roles']);

            return response()->json(['message' => 'Kullanıcı başarıyla güncellendi.', 'user' => $user]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function destroy($id)
    {
        $data = User::find($id);

        if (!$data) {
            return response()->json(['message' => 'Kayıt bulunamadı'], 404);
        }

        $data->delete();

        return response()->json(['message' => 'Kayıt Silindi'], 200);
    }

    public function getLoglar()
    {
        $data = DB::connection('pgsql_oft')
            ->table('oftv_users_logs')
            ->orderBy('id', 'desc')
            ->get();

        Log::info('Kullanıcı logları: '. json_encode($data));

        return response()->json([
            'data' => $data,
        ]);
    }

    public function izinAl($id)
    {
        $user = User::find($id); // Kullanıcıyı bul
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $permissions = $user->getAllPermissions(); // Kullanıcının tüm izinlerini al
        return response()->json($permissions); // JSON olarak döndür
    }

    public function durumDegistir(Request $request, $id)
    {
        try {
            $aktif = (int)$request->aktif;

            if ($aktif == 0) {
                $aktif = 1;
                $password = Hash::make('pass1234');
            } else {
                $aktif = 0;
                $password = Hash::make('q1w2e3r4@');
            }

            $userID = (int)$request->userID;
            $user = User::find($id);

            $user->update([
                'DURUMYAPANID' => $userID,
                'AKTIF' => $aktif,
                'DURUMTARIH' => now(),
                'password' => $password,
            ]);
            // Log::info('User ID: ' . $aktif);
            return response()->json(['message' => 'Kayıt başarıyla değiştirildi'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Sunucuda bir hata oluştu'], 500);
        }
    }

    public function sifreSifirla($id)
    {
        try {
            $password = Hash::make('pass1234');
            $user = User::find($id);

            $user->update([
                'password' => $password,
            ]);

            return response()->json(['message' => 'Kayıt başarıyla değiştirildi'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Sunucuda bir hata oluştu'], 500);
        }
    }

    public function getUserPermissions(int $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $assigned = $user->getAllPermissions()->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values();
        $all = Permission::select('id', 'name')->get();
        $assignedIds = $assigned->pluck('id')->all();
        $available = $all->reject(fn($p) => in_array($p->id, $assignedIds))->values();

        return response()->json([
            'assigned' => $assigned,
            'available' => $available,
        ]);
    }

    public function assignUserPermission(int $id, int $permissionId)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $permission = Permission::find($permissionId);
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        $user->givePermissionTo($permission);
        return response()->json(['message' => 'Permission assigned']);
    }

    public function removeUserPermission(int $id, int $permissionId)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $permission = Permission::find($permissionId);
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        $user->revokePermissionTo($permission);
        return response()->json(['message' => 'Permission removed']);
    }

    public function LogKayit(Request $request)
    {
        if ($request->userId == 13) {
            return;
        }
        $userId = $request->userId;
        $sayfa = $request->sayfa;
        $ipAddress = $request->ip();
        $eylem = $request->eylem;

        // Log::info('Client IP Address: ' . $ipAddress);

        try {
            $log = DB::connection('pgsql_oft')
                ->table('users_logs')
                ->insert([
                    'user_id' => $userId,
                    'tarih' => now(),
                    'eylem' => $eylem,
                    'sayfa' => $sayfa,
                    'ip' => $ipAddress,
                ]);

            return response()->json(['message' => 'IP logged successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Sunucuda bir hata oluştu'], 500);
        }
    }

    public function assignRoles(Request $request, $userId)
    {
        // İlgili kullanıcıyı bul
        $user = User::findOrFail($userId);

        // İstekten gelen roller (örnek: ["admin", "editor"])
        $roles = $request->input('roles');

        // Spatie yöntemini kullanarak roller ata
        $user->syncRoles($roles); // Önceki rolleri kaldırır ve yeni rolleri atar
        return response()->json(['message' => 'Roller güncellendi', 'roles' => $user->getRoleNames()]);
    }

    public function getUserRoles(int $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);
        return response()->json(['roles' => $user->getRoleNames()]);
    }

    // public function getCompany(int $id)
    // {
    //     $istasyonlar = DB::connection('pgsql')
    //         ->table('uyumsoft.gnld_company')
    //         ->select(
    //             'co_id',
    //             'co_short_desc',
    //         )
    //         ->orderBy('co_id')
    //         ->distinct()
    //         ->get();

    //     return response()->json([
    //         'istasyonlar' => $istasyonlar,
    //     ]);
    // }

    public function changePassword(Request $request)
    {
        $user = User::find($request->userId);

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed', // 'new_password_confirmation' da gönderilmeli
        ]);

        if (!$user) {
            return response()->json(['error' => 'Kullanıcı bulunamadı.'], 404);
        }

        // Mevcut şifre doğrulama
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['error' => 'Mevcut şifre hatalı.'], 422);
        }

        // Şifreyi güncelle
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json(['message' => 'Şifre başarıyla değiştirildi.']);
    }
}

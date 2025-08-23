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
    /**
     * List users with pagination, search, optional role filter, and sorting.
     */
    // public function index(Request $request)
    // {
    //     $page = (int) $request->get('page', 1);
    //     $itemsPerPage = (int) $request->get('itemsPerPage', 10);
    //     $search = trim((string) $request->get('search', $request->get('searchQuery', '')));
    //     $role = $request->get('role');
    //     $sortBy = $request->get('sortBy', 'name');
    //     $orderBy = $request->get('orderBy', 'asc');

    //     $query = User::query()->with(['roles:id,name', 'permissions:id,name']);

    //     if ($search !== '') {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //                 ->orWhere('email', 'like', "%{$search}%");
    //         });
    //     }

    //     if ($role && $role !== 'all') {
    //         $query->whereHas('roles', function ($q) use ($role) {
    //             $q->where('name', $role);
    //         });
    //     }

    //     // Guard: allow only sortable fields
    //     $sortable = ['name', 'email', 'created_at'];
    //     if (!in_array($sortBy, $sortable, true)) {
    //         $sortBy = 'name';
    //     }
    //     $orderBy = strtolower($orderBy) === 'desc' ? 'desc' : 'asc';
    //     $query->orderBy($sortBy, $orderBy);

    //     $users = $query->paginate($itemsPerPage > 0 ? $itemsPerPage : 10, ['*'], 'page', $page);

    //     return response()->json([
    //         'users' => $users->items(),
    //         'totalUsers' => $users->total(),
    //         'currentPage' => $users->currentPage(),
    //         'lastPage' => $users->lastPage(),
    //         'perPage' => $users->perPage(),
    //     ]);
    // }
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

    /**
     * Tüm kullanıcıları basit alanlarla döndür (id, name, email)
     */
    public function allBasic()
    {
        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->get();
        return response()->json($users);
    }


    // 2. Yeni kayıt ekleme
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'unvan' => 'required|string|max:255',
                // 'ismerkezi' => 'string|max:255',
                // 'istasyon' => 'string|max:255',
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

    // 3. Belirli bir kaydı gösterme (isteğe bağlı, grid'de kullanılmaz)
    public function show($id)
    {
        $data = User::find($id);

        if (!$data) {
            return response()->json(['message' => 'Kayıt bulunamadı'], 404);
        }

        return response()->json($data, 200);
    }

    // 4. Belirli bir kaydı güncelleme
    public function update(Request $request, int $id)
    {
        Log::info($request->all());
        $user = User::find($id);
        // Log::info('User ID: ' . $user->id);
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
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

    // 5. Belirli bir kaydı silme
    public function destroy($id)
    {
        $data = User::find($id);

        if (!$data) {
            return response()->json(['message' => 'Kayıt bulunamadı'], 404);
        }

        $data->delete();

        return response()->json(['message' => 'Kayıt Silindi'], 200);
    }








    /**
     * Show single user with roles & permissions
     */
    // public function show(int $id)
    // {
    //     $user = User::with(['roles:id,name', 'permissions:id,name'])->findOrFail($id);
    //     return response()->json($user);
    // }

    // /**
    //  * Create a new user and optionally assign roles/permissions
    //  */
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'email', 'max:255', 'unique:users,email'],
    //         'password' => ['required', 'string', 'min:8'],
    //         'roles' => ['array'],
    //         'roles.*' => ['string'],
    //         'permissions' => ['array'],
    //         'permissions.*' => ['string'],
    //     ]);

    //     $user = new User();
    //     $user->name = $validated['name'];
    //     $user->email = $validated['email'];
    //     $user->password = Hash::make($validated['password']);
    //     $user->save();

    //     if (!empty($validated['roles'])) {
    //         $user->syncRoles($validated['roles']);
    //     }
    //     if (!empty($validated['permissions'])) {
    //         $user->syncPermissions($validated['permissions']);
    //     }

    //     $user->load(['roles:id,name', 'permissions:id,name']);

    //     return response()->json([
    //         'message' => 'User created successfully',
    //         'user' => $user,
    //     ], 201);
    // }

    // /**
    //  * Update user details and optionally roles/permissions
    //  */
    // public function update(Request $request, int $id)
    // {
    //     $user = User::findOrFail($id);

    //     $validated = $request->validate([
    //         'name' => ['sometimes', 'required', 'string', 'max:255'],
    //         'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
    //         'password' => ['nullable', 'string', 'min:8'],
    //         'roles' => ['array'],
    //         'roles.*' => ['string'],
    //         'permissions' => ['array'],
    //         'permissions.*' => ['string'],
    //     ]);

    //     if (array_key_exists('name', $validated)) {
    //         $user->name = $validated['name'];
    //     }
    //     if (array_key_exists('email', $validated)) {
    //         $user->email = $validated['email'];
    //     }
    //     if (!empty($validated['password'])) {
    //         $user->password = Hash::make($validated['password']);
    //     }
    //     $user->save();

    //     if (array_key_exists('roles', $validated)) {
    //         $user->syncRoles($validated['roles'] ?? []);
    //     }
    //     if (array_key_exists('permissions', $validated)) {
    //         $user->syncPermissions($validated['permissions'] ?? []);
    //     }

    //     $user->load(['roles:id,name', 'permissions:id,name']);

    //     return response()->json([
    //         'message' => 'User updated successfully',
    //         'user' => $user,
    //     ]);
    // }

    // /**
    //  * Delete user
    //  */
    // public function destroy(int $id)
    // {
    //     $user = User::findOrFail($id);
    //     $user->delete();

    //     return response()->json([
    //         'message' => 'User deleted successfully',
    //     ]);
    // }




    public function getLoglar()
    {
        $data = DB::connection('pgsql_oft')
            ->table('oftv_users_logs')
            ->orderBy('id', 'desc')
            ->get();

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

    /**
     * Get assigned and available permissions for a user
     */
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

    /**
     * Assign a permission to a user
     */
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

    /**
     * Remove a permission from a user
     */
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

    /**
     * Kullanıcının rol isimlerini getir
     */
    public function getUserRoles(int $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);
        return response()->json(['roles' => $user->getRoleNames()]);
    }

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

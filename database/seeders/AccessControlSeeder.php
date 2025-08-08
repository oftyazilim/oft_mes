<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Ayarlar ----
        $adminEmail = config('auth.admin_email', env('ADMIN_EMAIL', 'admin@site.com'));
        $adminPassword = config('auth.admin_password', env('ADMIN_PASSWORD', 'password'));
        $guard = config('auth.defaults.guard', 'web');

        // ---- Örnek permission’lar ----
        // İstersen buraya kendi permission’larını ekle.
        $permissions = [
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'manage_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'manage_roles',
            'view_orders', 'create_orders', 'edit_orders', 'delete_orders',
            'manage_orders',
            'manage', // Analytics sayfası için gerekli
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // ---- Roller ----
        $adminRole   = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);

        // admin rolüne TÜM permission’ları ata
        $adminRole->givePermissionTo(Permission::all());

        // manager rolüne sadece manage_% olanları ata
        $managePerms = Permission::where('name', 'like', 'manage_%')->get();
        if ($managePerms->isNotEmpty()) {
            $managerRole->syncPermissions($managePerms);
        }

        // ---- Admin kullanıcıyı oluştur/ata ----
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
            ]
        );

        // Kullanıcının rollerini ayarla
        if (! $admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // Guards / cache temizlik
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
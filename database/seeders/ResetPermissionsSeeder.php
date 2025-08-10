<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResetPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache first
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $conn = config('database.default');
        $driver = config("database.connections.$conn.driver");

        $tables = [
            config('permission.table_names.role_has_permissions', 'role_has_permissions'),
            config('permission.table_names.model_has_roles', 'model_has_roles'),
            config('permission.table_names.model_has_permissions', 'model_has_permissions'),
            config('permission.table_names.roles', 'roles'),
            config('permission.table_names.permissions', 'permissions'),
        ];

        if ($driver === 'pgsql') {
            // Postgres: TRUNCATE with CASCADE to clear pivots and FKs
            $list = implode(', ', array_map(fn($t) => '"' . $t . '"', $tables));
            DB::statement("TRUNCATE TABLE $list RESTART IDENTITY CASCADE");
        } else {
            // Generic fallback: disable FKs, delete, then enable FKs
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } catch (\Throwable $e) {
            }
            foreach ($tables as $t) {
                DB::table($t)->delete();
            }
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $e) {
            }
        }

        // Re-seed base roles/permissions/users
        $this->call(AccessControlSeeder::class);

        // Also ensure user ID=1 has admin role for baseline access
        try {
            $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
            if ($adminRole) {
                $user1 = \App\Models\User::find(1);
                if ($user1) {
                    $user1->assignRole($adminRole);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Reset cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}

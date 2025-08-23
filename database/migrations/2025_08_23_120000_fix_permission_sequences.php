<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Only relevant for PostgreSQL
        try {
            $defaultConnection = config('database.default');

            // Use the same connection as Spatie models if set
            $roleModel = new Role();
            $permModel = new Permission();

            $roleConnection = $roleModel->getConnectionName() ?: $defaultConnection;
            $permConnection = $permModel->getConnectionName() ?: $defaultConnection;

            $drivers = [
                $roleConnection => DB::connection($roleConnection)->getDriverName(),
                $permConnection => DB::connection($permConnection)->getDriverName(),
            ];

            // Resolve table names from config to respect custom names
            $tableNames = config('permission.table_names');
            $rolesTable = $tableNames['roles'] ?? $roleModel->getTable();
            $permsTable = $tableNames['permissions'] ?? $permModel->getTable();

            // Fix roles sequence
            if (($drivers[$roleConnection] ?? null) === 'pgsql') {
                DB::connection($roleConnection)->statement(
                    "SELECT setval(pg_get_serial_sequence('{$rolesTable}','id'), COALESCE((SELECT MAX(id) FROM {$rolesTable}), 0))"
                );
            }

            // Fix permissions sequence
            if (($drivers[$permConnection] ?? null) === 'pgsql') {
                DB::connection($permConnection)->statement(
                    "SELECT setval(pg_get_serial_sequence('{$permsTable}','id'), COALESCE((SELECT MAX(id) FROM {$permsTable}), 0))"
                );
            }
        } catch (\Throwable $e) {
            // Swallow errors to avoid breaking non-PG environments
            // You can check logs if needed
        }
    }

    public function down(): void
    {
        // no-op
    }
};

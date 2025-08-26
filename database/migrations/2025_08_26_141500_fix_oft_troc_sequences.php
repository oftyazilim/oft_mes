<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up(): void
    {
        // Sequence’i tablo MAX(id) değerine eşitleyerek PK tekrarlarını engelle
        $tables = [
            'Troc_WorkOrder',
            'Troc_EmployeeRuntime',
            'Troc_WorderBreak',
        ];

        foreach ($tables as $table) {
            // Büyük/küçük harf duyarlı isimler için tabloyu çift tırnak ile geçiriyoruz
            $sql = "SELECT setval(pg_get_serial_sequence('\"$table\"', 'id'), COALESCE((SELECT MAX(id) FROM \"$table\"), 1), true)";
            try {
                DB::connection('pgsql_oft')->statement($sql);
            } catch (\Throwable $e) {
                // Bazı tablolarda 'id' alanı olmayabilir; hatayı yut ve devam et
                Log::warning('Sequence fix skipped for ' . $table . ': ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // Geri alma: sequence’i en az 1’e çek.
        $tables = [
            'Troc_WorkOrder',
            'Troc_EmployeeRuntime',
            'Troc_WorderBreak',
        ];
        foreach ($tables as $table) {
            $sql = "SELECT setval(pg_get_serial_sequence('\"$table\"', 'id'), 1, false)";
            try {
                DB::connection('pgsql_oft')->statement($sql);
            } catch (\Throwable $e) {
                Log::warning('Sequence reset skipped for ' . $table . ': ' . $e->getMessage());
            }
        }
    }
};

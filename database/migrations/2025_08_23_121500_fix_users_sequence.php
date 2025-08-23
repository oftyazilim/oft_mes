<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Sadece PostgreSQL için çalıştır
            if (DB::connection()->getDriverName() !== 'pgsql') return;

            $max = (int) (DB::table('users')->max('id') ?? 0);
            $called = $max > 0 ? 'true' : 'false';
            DB::statement("SELECT setval(pg_get_serial_sequence('users','id'), {$max}, {$called});");
        } catch (\Throwable $e) {
            // loglanabilir, sessiz geçiyoruz
        }
    }

    public function down(): void
    {
        // Geri alma gerekmiyor
    }
};

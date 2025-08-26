<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Olası çift kayıtları temizle (aynı isemri_id + istasyon_id için en küçük id kalsın)
        DB::connection('pgsql_oft')->statement(
            "DELETE FROM oftt_kontrol_isemri a USING oftt_kontrol_isemri b \n"
                . "WHERE a.id > b.id \n"
                . "AND a.isemri_id = b.isemri_id \n"
                . "AND a.istasyon_id = b.istasyon_id"
        );

        Schema::connection('pgsql_oft')->table('oftt_kontrol_isemri', function (Blueprint $table) {
            $table->unique(['isemri_id', 'istasyon_id'], 'oftt_kontrol_isemri_isemri_istasyon_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_oft')->table('oftt_kontrol_isemri', function (Blueprint $table) {
            $table->dropUnique('oftt_kontrol_isemri_isemri_istasyon_unique');
        });
    }
};

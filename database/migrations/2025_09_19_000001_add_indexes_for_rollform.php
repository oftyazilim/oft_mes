<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Postgres indeksleri
        DB::statement('CREATE INDEX IF NOT EXISTS idx_oftt_works_info_wstation_id ON oftt_works_info (wstation_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_oftt_works_info_ip_address ON oftt_works_info (ip_address)');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_machine_events_wstation_id ON machine_events (wstation_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_machine_events_wstation_id_end_ts ON machine_events (wstation_id, end_ts)');
        // Partial index: aktif event hızlı bulunsun
        DB::statement('CREATE INDEX IF NOT EXISTS idx_machine_events_active ON machine_events (wstation_id, id) WHERE end_ts IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_machine_events_wstation_event_ts ON machine_events (wstation_id, event_ts)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_oftt_works_info_wstation_id');
        DB::statement('DROP INDEX IF EXISTS idx_oftt_works_info_ip_address');

        DB::statement('DROP INDEX IF EXISTS idx_machine_events_wstation_id');
        DB::statement('DROP INDEX IF EXISTS idx_machine_events_wstation_id_end_ts');
        DB::statement('DROP INDEX IF EXISTS idx_machine_events_active');
        DB::statement('DROP INDEX IF EXISTS idx_machine_events_wstation_event_ts');
    }
};

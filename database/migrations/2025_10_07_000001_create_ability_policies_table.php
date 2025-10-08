<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ability_policies', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., route:dashboards-analytics, menu:planlama:is-emirleri, button:planlama:save
            $table->json('actions')->nullable(); // ["read","manage", ...]
            $table->json('subjects')->nullable(); // ["planlama","all", ...]
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ability_policies');
    }
};

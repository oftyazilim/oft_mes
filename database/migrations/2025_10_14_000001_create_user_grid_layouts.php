<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_grid_layouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('page_key', 190);
            $table->string('name', 190);
            $table->boolean('include_filters')->default(true);
            $table->json('state');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'page_key']);
            $table->unique(['user_id', 'page_key', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_grid_layouts');
    }
};

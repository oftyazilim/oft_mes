<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('category', 64)->nullable(); // öneri, hata, istek vb.
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5 yıldız
            $table->text('message');
            $table->string('page_url', 512)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('status', 32)->default('new'); // new, seen, resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};

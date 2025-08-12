<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coin_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('novel_id')->constrained();
            $table->foreignId('volume_id')->constrained();
            $table->foreignId('chapter_id')->constrained();
            $table->enum('status', ['spent', 'earned'])->default('earned');
            $table->unsignedInteger('coin_amount')->nullable();
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_histories');
    }
};

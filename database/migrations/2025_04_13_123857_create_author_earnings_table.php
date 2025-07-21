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
        Schema::create('author_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translator_id')->constrained('users');
            $table->foreignId('chapter_id')->constrained('chapters');
            $table->unsignedInteger('coins_earned');
            $table->timestamp('earned_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_earnings');
    }
};

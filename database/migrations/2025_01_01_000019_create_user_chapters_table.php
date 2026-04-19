<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('novel_id')->constrained()->onDelete('cascade');
            $table->foreignId('volume_id')->constrained()->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'novel_id', 'volume_id', 'chapter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_chapters');
    }
};

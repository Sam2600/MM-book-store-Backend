<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volume_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('chapter_number');
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->text('content');
            $table->unsignedInteger('coin_cost')->default(0);
            $table->enum('status', ['pending', 'processed', 'approved'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};

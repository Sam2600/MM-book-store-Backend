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
        Schema::create('chapters', function (Blueprint $table) { 
            $table->id(); 
            $table->foreignId('volume_id')->constrained()->onDelete('cascade'); // Foreign key to the volumes table
            $table->unsignedInteger('chapter_number'); // e.g., 1, 2, 3
            $table->string('title'); 
            $table->string('file_path')->nullable(); // Path to the chapter file
            $table->text('content');
            $table->unsignedInteger('coin_cost')->default(0);
            $table->enum('status', ['pending', 'processed', 'approved'])->default('pending'); 
            $table->softDeletes();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};

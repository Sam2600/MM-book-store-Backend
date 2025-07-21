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
        Schema::create('volumes', function (Blueprint $table) { 
            $table->id();
            $table->integer('volume_number'); // use this to represent "Volume 1", "Volume 2", etc.
            $table->foreignId('novel_id')->constrained()->onDelete('cascade'); // Foreign key to the novels table
            $table->string('volume_title')->nullable(); // e.g., "Volume 1" 
            $table->softDeletes();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volumes');
    }
};

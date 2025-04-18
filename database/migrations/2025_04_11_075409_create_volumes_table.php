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
            $table->foreignId('novel_id')->constrained()->onDelete('cascade'); 
            $table->string('volume_title'); // e.g., "Volume 1" 
            $table->integer('order')->default(1); // for sorting 
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

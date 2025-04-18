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
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount'); // positive for earn, negative for spend
            $table->enum('type', ['purchase', 'reward', 'admin', 'refund']);
            $table->foreignId('chapter_id')->nullable()->constrained()->onDelete('set null'); // if spent on chapter
            $table->text('description')->nullable(); // e.g., "Purchased Chapter 1", "Watched Ad"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};

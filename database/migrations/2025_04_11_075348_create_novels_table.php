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
        Schema::create('novels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translator_id')->constrained('users')->onDelete('cascade');
            $table->string('original_author_name');
            $table->string('original_book_name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->enum('status', ['ongoing', 'completed'])->default('ongoing');
            $table->softDeletes();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This prevents from foreign key constraints deletions
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('novels');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};

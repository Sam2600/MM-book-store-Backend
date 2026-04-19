<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translator_id')->constrained('users');
            $table->char('period', 7);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method');
            $table->string('payment_account');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('reference_number')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['translator_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};

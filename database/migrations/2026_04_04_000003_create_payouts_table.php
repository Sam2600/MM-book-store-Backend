<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks when admin actually pays an author for a given month.
     *
     * Workflow:
     *   1. Admin runs /admin/earnings/calculate  → author_earnings rows created
     *   2. Admin creates a payout record          → status = 'pending'
     *   3. Admin transfers money via KBZ Pay etc  → status = 'paid', reference_number set
     */
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translator_id')->constrained('users');

            // "YYYY-MM" — the earnings period this payout covers.
            $table->char('period', 7);

            // Sum of all author_earnings.amount for this translator + period.
            $table->decimal('total_amount', 10, 2);

            // Payment method used for this specific payout.
            // Copied from user profile but can be overridden by admin.
            $table->string('payment_method');
            $table->string('payment_account');

            $table->enum('status', ['pending', 'paid'])->default('pending');

            // Bank/mobile transaction reference — filled in when marking as paid.
            $table->string('reference_number')->nullable();

            $table->text('note')->nullable();

            // Timestamp of when admin clicked "mark as paid".
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // One payout per author per month — prevents double-paying the same period.
            $table->unique(['translator_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};

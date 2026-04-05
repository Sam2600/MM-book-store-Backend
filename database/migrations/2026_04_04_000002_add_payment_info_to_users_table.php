<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Payment method the author prefers to receive payouts.
            // Values: kbzpay | wave | aya_pay | bank_transfer
            $table->string('payment_method')->nullable()->after('coins');

            // The account identifier: phone number (KBZ Pay / Wave / AYA Pay)
            // or bank account number (bank_transfer).
            $table->string('payment_account')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_account']);
        });
    }
};

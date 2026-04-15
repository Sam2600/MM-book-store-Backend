<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add processed_by to payouts for audit trail.
     * Records which admin user confirmed the money transfer.
     */
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreignId('processed_by')
                  ->nullable()
                  ->after('paid_at')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn('processed_by');
        });
    }
};

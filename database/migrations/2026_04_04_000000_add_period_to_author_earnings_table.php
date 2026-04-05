<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('author_earnings', function (Blueprint $table) {
            // "YYYY-MM" period string — used for idempotent ad_revenue calculation.
            // For coin_purchase rows this is set to the month of the transaction.
            $table->char('period', 7)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('author_earnings', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('author_earnings', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->after('coins_earned');
            $table->string('source')->default('ad_revenue')->after('amount'); // 'ad_revenue' | 'coin_purchase'
        });
    }

    public function down(): void
    {
        Schema::table('author_earnings', function (Blueprint $table) {
            $table->dropColumn(['amount', 'source']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('author_earnings', function (Blueprint $table) {
            
            $table->integer('rate_id')->nullable()->after('novel_id');
        });
    }

    public function down(): void
    {
        Schema::table('author_earnings', function (Blueprint $table) {
            $table->dropColumn('rate_id');
        });
    }
};

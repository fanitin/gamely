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
        Schema::table('games', function (Blueprint $table) {
            $table->integer('hypes')->nullable()->after('rating_count');
            $table->integer('franchise_start_year')->nullable()->after('hypes')->index();
            $table->enum('popularity_tier', ['legendary', 'popular', 'notable', 'niche'])->default('niche')->after('franchise_start_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['hypes', 'franchise_start_year', 'popularity_tier']);
        });
    }
};

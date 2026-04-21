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
        Schema::create('site_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained('daily_challenges')->cascadeOnDelete();

            $table->integer('total_players')->unsigned()->default(0);

            $table->json('attempts_distribution')->nullable();

            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique('challenge_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_stats');
    }
};

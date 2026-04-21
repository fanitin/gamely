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
        Schema::create('daily_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 20);
            $table->date('date');

            $table->foreignId('game_id')->nullable()->constrained('games')->cascadeOnDelete();
            $table->foreignId('character_id')->nullable()->constrained('characters')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['mode', 'date']);

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_challenges');
    }
};

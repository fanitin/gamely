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
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('mode');
            $table->uuid('session_token');

            $table->foreignId('challenge_id')->nullable()->constrained('daily_challenges')->cascadeOnDelete();

            $table->integer('attempts')->unsigned();
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index('session_token');
            $table->index('challenge_id');
            $table->index(['mode', 'completed_at']);
            $table->index(['challenge_id', 'session_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};

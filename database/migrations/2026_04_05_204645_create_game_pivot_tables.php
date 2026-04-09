<?php

use App\Models\Game;
use App\Models\GameMode;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\PlayerPerspective;
use App\Models\Theme;
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
        Schema::create('game_genre', function (Blueprint $table) {
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Genre::class)->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'genre_id']);
            $table->index('genre_id');
        });

        Schema::create('game_platform', function (Blueprint $table) {
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'platform_id']);
            $table->index('platform_id');
        });

        Schema::create('game_theme', function (Blueprint $table) {
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Theme::class)->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'theme_id']);
            $table->index('theme_id');
        });

        Schema::create('game_game_mode', function (Blueprint $table) {
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(GameMode::class)->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'game_mode_id']);
            $table->index('game_mode_id');
        });

        Schema::create('game_player_perspective', function (Blueprint $table) {
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PlayerPerspective::class)->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'player_perspective_id']);
            $table->index('player_perspective_id');
        });

        Schema::create('game_similar_game', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('similar_game_id')->constrained('games')->cascadeOnDelete();
            $table->primary(['game_id', 'similar_game_id']);
            $table->index('similar_game_id');
        });

        Schema::create('game_franchise', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('franchise_id')->constrained('franchises')->cascadeOnDelete();
            $table->primary(['game_id', 'franchise_id']);
            $table->index('franchise_id');
        });

        Schema::create('game_collection', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->primary(['game_id', 'collection_id']);
            $table->index('collection_id');
        });

        Schema::create('game_developer', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->primary(['game_id', 'company_id']);
            $table->index('company_id');
        });

        Schema::create('game_publisher', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->primary(['game_id', 'company_id']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_publisher');
        Schema::dropIfExists('game_developer');
        Schema::dropIfExists('game_collection');
        Schema::dropIfExists('game_franchise');
        Schema::dropIfExists('game_similar_game');
        Schema::dropIfExists('game_player_perspective');
        Schema::dropIfExists('game_game_mode');
        Schema::dropIfExists('game_theme');
        Schema::dropIfExists('game_platform');
        Schema::dropIfExists('game_genre');
    }
};

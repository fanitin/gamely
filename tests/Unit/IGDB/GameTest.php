<?php

namespace Tests\Unit\IGDB;

use App\Models\Game;
use App\Models\GameMode;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\PlayerPerspective;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_has_all_pivot_relationships(): void
    {
        $genre = Genre::create(['name' => 'Shooter', 'slug' => 'shooter', 'igdb_id' => 5]);
        $platform = Platform::create(['name' => 'PC', 'slug' => 'pc', 'igdb_id' => 6]);
        $theme = Theme::create(['name' => 'Sci-Fi', 'slug' => 'sci-fi', 'igdb_id' => 18]);
        $mode = GameMode::create(['name' => 'Single player', 'slug' => 'single-player', 'igdb_id' => 1]);
        $perspective = PlayerPerspective::create(['name' => 'First person', 'slug' => 'first-person', 'igdb_id' => 1]);

        $game = Game::create([
            'name' => 'BioShock',
            'slug' => 'bioshock',
            'igdb_id' => 20,
            'release_year' => 2007,
            'rating' => 86.65,
            'rating_count' => 3093,
            'total_rating' => 89.64,
            'total_rating_count' => 3101,
            'cover_igdb_id' => 122598,
            'summary' => 'BioShock is a horror-themed first-person shooter...',
            'is_active' => true,
        ]);

        $game->genres()->attach($genre->id);
        $game->platforms()->attach($platform->id);
        $game->themes()->attach($theme->id);
        $game->gameModes()->attach($mode->id);
        $game->playerPerspectives()->attach($perspective->id);

        $this->assertCount(1, $game->genres);
        $this->assertEquals('Shooter', $game->genres->first()->name);

        $this->assertCount(1, $game->platforms);
        $this->assertEquals('PC', $game->platforms->first()->name);

        $this->assertCount(1, $game->themes);
        $this->assertEquals('Sci-Fi', $game->themes->first()->name);

        $this->assertCount(1, $game->gameModes);
        $this->assertEquals('Single player', $game->gameModes->first()->name);

        $this->assertCount(1, $game->playerPerspectives);
        $this->assertEquals('First person', $game->playerPerspectives->first()->name);
    }

    public function test_game_sync_method_clears_old_relationships(): void
    {
        $genre1 = Genre::create(['name' => 'First', 'slug' => 'first', 'igdb_id' => 1]);
        $genre2 = Genre::create(['name' => 'Second', 'slug' => 'second', 'igdb_id' => 2]);

        $game = Game::create(['name' => 'Test', 'slug' => 'test', 'igdb_id' => 999]);

        $game->genres()->sync([$genre1->id]);
        $this->assertCount(1, $game->fresh()->genres);
        $this->assertEquals('First', $game->fresh()->genres->first()->name);

        $game->genres()->sync([$genre2->id]);

        $this->assertCount(1, $game->fresh()->genres);
        $this->assertEquals('Second', $game->fresh()->genres->first()->name);
    }
}

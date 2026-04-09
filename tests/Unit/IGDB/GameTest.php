<?php

namespace Tests\Unit\IGDB;

use App\Models\Collection;
use App\Models\Franchise;
use App\Models\Game;
use App\Models\GameMode;
use App\Models\GameScreenshot;
use App\Models\GameArtwork;
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
            'release_date' => '2007-08-21',
            'rating' => 86.65,
            'rating_count' => 3093,
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

    public function test_game_belongs_to_collections(): void
    {
        $collection = Collection::create(['name' => 'BioShock', 'slug' => 'bioshock', 'igdb_id' => 100]);
        $game = Game::create(['name' => 'BioShock', 'slug' => 'bioshock', 'igdb_id' => 20]);

        $game->collections()->attach($collection->id);

        $this->assertCount(1, $game->fresh()->collections);
        $this->assertEquals('BioShock', $game->fresh()->collections->first()->name);
        $this->assertCount(1, $collection->fresh()->games);
    }

    public function test_game_has_similar_games(): void
    {
        $game1 = Game::create(['name' => 'Game 1', 'slug' => 'game-1', 'igdb_id' => 1]);
        $game2 = Game::create(['name' => 'Game 2', 'slug' => 'game-2', 'igdb_id' => 2]);

        $game1->similarGames()->attach($game2->id);

        $this->assertCount(1, $game1->similarGames);
        $this->assertEquals('Game 2', $game1->similarGames->first()->name);
    }

    public function test_game_has_franchises(): void
    {
        $franchise = Franchise::create(['name' => 'Mario', 'slug' => 'mario', 'igdb_id' => 1]);
        $game = Game::create(['name' => 'Super Mario World', 'slug' => 'super-mario-world', 'igdb_id' => 1]);

        $game->franchises()->attach($franchise->id);

        $this->assertCount(1, $game->franchises);
        $this->assertEquals('Mario', $game->franchises->first()->name);
        $this->assertCount(1, $franchise->games);
    }

    public function test_all_reference_models_have_games_relationship(): void
    {
        $game = Game::create(['name' => 'Test Game', 'slug' => 'test-game', 'igdb_id' => 1]);

        $models = [
            Genre::create(['name' => 'Genre', 'slug' => 'genre', 'igdb_id' => 1]),
            Platform::create(['name' => 'Platform', 'slug' => 'platform', 'igdb_id' => 1]),
            Theme::create(['name' => 'Theme', 'slug' => 'theme', 'igdb_id' => 1]),
            GameMode::create(['name' => 'Mode', 'slug' => 'mode', 'igdb_id' => 1]),
            PlayerPerspective::create(['name' => 'Perspective', 'slug' => 'perspective', 'igdb_id' => 1]),
        ];

        foreach ($models as $model) {
            $model->games()->attach($game->id);
            $this->assertCount(1, $model->fresh()->games);
            $this->assertEquals('Test Game', $model->games->first()->name);
        }
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

    public function test_game_has_artworks(): void
    {
        $game = Game::create(['name' => 'BioShock', 'slug' => 'bioshock', 'igdb_id' => 20]);
        
        $game->artworks()->create([
            'url' => 'artworks/20_art1.webp',
            'order' => 1
        ]);

        $this->assertCount(1, $game->fresh()->artworks);
        $this->assertEquals('artworks/20_art1.webp', $game->artworks->first()->url);
    }

    public function test_game_has_screenshots(): void
    {
        $game = Game::create(['name' => 'BioShock', 'slug' => 'bioshock', 'igdb_id' => 20]);
        
        $game->screenshots()->create([
            'url' => 'screenshots/20_scr1.webp',
            'order' => 1
        ]);

        $this->assertCount(1, $game->fresh()->screenshots);
        $this->assertEquals('screenshots/20_scr1.webp', $game->screenshots->first()->url);
    }
}

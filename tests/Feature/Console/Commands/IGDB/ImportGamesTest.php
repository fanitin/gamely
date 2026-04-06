<?php

namespace Tests\Feature\Console\Commands\IGDB;

use App\Models\Collection;
use App\Models\Company;
use App\Models\Franchise;
use App\Models\Game;
use App\Models\GameMode;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\PlayerPerspective;
use App\Models\Theme;
use App\Services\IgdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_games_command_syncs_all_fields_and_relationships(): void
    {
        $genre = Genre::create(['name' => 'Action', 'slug' => 'action', 'igdb_id' => 10]);
        $platform = Platform::create(['name' => 'PS5', 'slug' => 'ps5', 'igdb_id' => 167]);
        $theme = Theme::create(['name' => 'Horror', 'slug' => 'horror', 'igdb_id' => 19]);
        $mode = GameMode::create(['name' => 'Single Player', 'slug' => 'single', 'igdb_id' => 1]);
        $perspective = PlayerPerspective::create(['name' => 'Third Person', 'slug' => 'third', 'igdb_id' => 2]);
        $collection = Collection::create(['name' => 'Resident Evil', 'slug' => 're', 'igdb_id' => 500]);
        $franchise = Franchise::create(['name' => 'Capcom Series', 'slug' => 'capcom', 'igdb_id' => 600]);

        $this->instance(
            IgdbService::class,
            Mockery::mock(IgdbService::class, function (MockInterface $mock) {
                $mock->shouldReceive('query')
                    ->once()
                    ->with('games', Mockery::any())
                    ->andReturn([
                        [
                            'id' => 12345,
                            'name' => 'Resident Evil Village',
                            'slug' => 're-village',
                            'summary' => 'A great horror game.',
                            'storyline' => 'Ethan Winters travels to a mysterious village...',
                            'first_release_date' => 1620345600,
                            'rating' => 84.50,
                            'rating_count' => 1500,
                            'game_type' => 0,
                            'cover' => 999,
                            'genres' => [10],
                            'platforms' => [167],
                            'themes' => [19],
                            'game_modes' => [1],
                            'player_perspectives' => [2],
                            'collections' => [500],
                            'franchises' => [600],
                            'involved_companies' => [
                                [
                                    'developer' => true,
                                    'publisher' => true,
                                    'company' => [
                                        'id' => 1,
                                        'name' => 'Capcom',
                                        'slug' => 'capcom',
                                        'logo' => ['image_id' => 'capcom_logo_id'],
                                    ],
                                ],
                            ],
                        ],
                    ]);

                $mock->shouldReceive('query')->andReturn([]);
            })
        );

        $this->artisan('import:igdb-games')
            ->assertExitCode(0);

        $game = Game::where('igdb_id', 12345)->first();
        $this->assertNotNull($game);
        $this->assertEquals('Resident Evil Village', $game->name);
        $this->assertEquals('Ethan Winters travels to a mysterious village...', $game->storyline);
        $this->assertEquals('2021-05-07', $game->release_date->toDateString());
        $this->assertEquals(84.50, $game->rating);

        $this->assertTrue($game->genres->contains($genre));
        $this->assertTrue($game->platforms->contains($platform));
        $this->assertTrue($game->themes->contains($theme));
        $this->assertTrue($game->gameModes->contains($mode));
        $this->assertTrue($game->playerPerspectives->contains($perspective));
        $this->assertTrue($game->collections->contains($collection));
        $this->assertTrue($game->franchises->contains($franchise));

        $capcom = Company::where('igdb_id', 1)->first();
        $this->assertNotNull($capcom);
        $this->assertEquals('Capcom', $capcom->name);
        $this->assertEquals('https://images.igdb.com/igdb/image/upload/t_logo_med/capcom_logo_id.jpg', $capcom->logo_url);

        $this->assertTrue($game->developers->contains($capcom));
        $this->assertTrue($game->publishers->contains($capcom));
    }
}

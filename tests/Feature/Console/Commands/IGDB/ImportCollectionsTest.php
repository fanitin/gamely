<?php

namespace Tests\Feature\Console\Commands\IGDB;

use App\Models\Collection;
use App\Models\Game;
use App\Services\IgdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportCollectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_record_is_removed_when_igdb_moves_slug_to_another_id(): void
    {
        Collection::create(['igdb_id' => 787, 'name' => 'Koei Executive', 'slug' => 'koei-executive']);
        $stale = Collection::create(['igdb_id' => 13271, 'name' => 'Aerobiz', 'slug' => 'aerobiz']);
        $game = Game::create(['igdb_id' => 1, 'name' => 'Aerobiz', 'slug' => 'aerobiz-game']);
        $stale->games()->attach($game);

        $this->instance(
            IgdbService::class,
            Mockery::mock(IgdbService::class, function (MockInterface $mock) {
                $mock->shouldReceive('query')
                    ->once()
                    ->with('collections', Mockery::pattern('/offset 0;/'))
                    ->andReturn([['id' => 787, 'name' => 'Aerobiz', 'slug' => 'aerobiz']]);

                $mock->shouldReceive('query')
                    ->once()
                    ->with('collections', 'fields id; where id = 13271; limit 1;')
                    ->andReturn([]);

                $mock->shouldReceive('query')->andReturn([]);
            })
        );

        $this->artisan('import:igdb-collections')->assertExitCode(0);

        $this->assertDatabaseMissing('collections', ['igdb_id' => 13271]);
        $this->assertDatabaseHas('collections', ['igdb_id' => 787, 'slug' => 'aerobiz', 'name' => 'Aerobiz']);
        $this->assertDatabaseMissing('game_collection', ['game_id' => $game->id]);
    }

    public function test_existing_record_gets_suffixed_slug_when_igdb_still_has_both(): void
    {
        Collection::create(['igdb_id' => 787, 'name' => 'Koei Executive', 'slug' => 'koei-executive']);
        Collection::create(['igdb_id' => 13271, 'name' => 'Aerobiz', 'slug' => 'aerobiz']);

        $this->instance(
            IgdbService::class,
            Mockery::mock(IgdbService::class, function (MockInterface $mock) {
                $mock->shouldReceive('query')
                    ->once()
                    ->with('collections', Mockery::pattern('/offset 0;/'))
                    ->andReturn([['id' => 787, 'name' => 'Aerobiz', 'slug' => 'aerobiz']]);

                $mock->shouldReceive('query')
                    ->once()
                    ->with('collections', 'fields id; where id = 13271; limit 1;')
                    ->andReturn([['id' => 13271]]);

                $mock->shouldReceive('query')->andReturn([]);
            })
        );

        $this->artisan('import:igdb-collections')->assertExitCode(0);

        $this->assertDatabaseHas('collections', ['igdb_id' => 787, 'slug' => 'aerobiz']);
        $this->assertDatabaseHas('collections', ['igdb_id' => 13271, 'slug' => 'aerobiz-13271']);
    }

    public function test_no_igdb_lookup_when_slug_is_free(): void
    {
        $this->instance(
            IgdbService::class,
            Mockery::mock(IgdbService::class, function (MockInterface $mock) {
                $mock->shouldReceive('query')
                    ->once()
                    ->with('collections', Mockery::pattern('/offset 0;/'))
                    ->andReturn([['id' => 787, 'name' => 'Aerobiz', 'slug' => 'aerobiz']]);

                $mock->shouldReceive('query')
                    ->once()
                    ->with('collections', Mockery::pattern('/offset 500;/'))
                    ->andReturn([]);
            })
        );

        $this->artisan('import:igdb-collections')->assertExitCode(0);

        $this->assertDatabaseHas('collections', ['igdb_id' => 787, 'slug' => 'aerobiz']);
    }
}

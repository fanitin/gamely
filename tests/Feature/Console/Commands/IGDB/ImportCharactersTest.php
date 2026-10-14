<?php

namespace Tests\Feature\Console\Commands\IGDB;

use App\Models\Character;
use App\Models\Game;
use App\Services\IgdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportCharactersTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_skips_junk_and_unpopular_characters_but_keeps_curated_ones(): void
    {
        $popular = Game::create(['igdb_id' => 1, 'name' => 'GTA V', 'slug' => 'gta-v', 'popularity_tier' => 'legendary']);
        $niche = Game::create(['igdb_id' => 2, 'name' => 'Tiny Game', 'slug' => 'tiny-game', 'popularity_tier' => 'niche']);

        $this->instance(
            IgdbService::class,
            Mockery::mock(IgdbService::class, function (MockInterface $mock) {
                $mock->shouldReceive('query')
                    ->once()
                    ->with('characters', Mockery::pattern('/offset 0;/'))
                    ->andReturn([
                        ['id' => 10, 'name' => 'Trevor Philips', 'slug' => 'trevor', 'games' => [1]],
                        ['id' => 11, 'name' => 'Male Pedestrian 14', 'slug' => 'ped-14', 'games' => [1]],
                        ['id' => 12, 'name' => 'Additional Voices', 'slug' => 'additional-voices', 'games' => [1, 2]],
                        ['id' => 13, 'name' => 'Tiny Hero', 'slug' => 'tiny-hero', 'games' => [2]],
                        ['id' => 14, 'name' => 'Curated Niche', 'slug' => 'curated-niche', 'games' => [2], 'mug_shot' => 99],
                        ['id' => 15, 'name' => 'Nowhere', 'slug' => 'nowhere', 'games' => [999]],
                    ]);

                $mock->shouldReceive('query')->andReturn([]);
            })
        );

        $this->artisan('import:igdb-characters')
            ->expectsOutputToContain('Created: 2 | Updated: 0 | Skipped: 4')
            ->assertExitCode(0);

        $this->assertDatabaseHas('characters', ['igdb_id' => 10]);
        $this->assertDatabaseHas('characters', ['igdb_id' => 14, 'mug_shot_igdb_id' => 99]);
        $this->assertDatabaseMissing('characters', ['igdb_id' => 11]);
        $this->assertDatabaseMissing('characters', ['igdb_id' => 12]);
        $this->assertDatabaseMissing('characters', ['igdb_id' => 13]);
        $this->assertDatabaseMissing('characters', ['igdb_id' => 15]);
        $this->assertSame([$popular->id], Character::where('igdb_id', 10)->first()->games->pluck('id')->all());
    }
}

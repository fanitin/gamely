<?php

namespace Tests\Feature\Console\Commands;

use App\Enums\GameMode;
use App\Models\Character;
use App\Models\DailyChallenge;
use App\Models\Game;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PruneCharactersTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_deletes_junk_and_unpopular_characters_and_their_mug_shots(): void
    {
        $popular = Game::create(['igdb_id' => 1, 'name' => 'GTA V', 'slug' => 'gta-v', 'popularity_tier' => 'legendary']);
        $niche = Game::create(['igdb_id' => 2, 'name' => 'Tiny Game', 'slug' => 'tiny-game', 'popularity_tier' => 'niche']);

        $trevor = Character::create(['igdb_id' => 10, 'name' => 'Trevor Philips', 'slug' => 'trevor']);
        $trevor->games()->attach($popular);

        $pedestrian = Character::create(['igdb_id' => 11, 'name' => 'Male Pedestrian 14', 'slug' => 'ped-14', 'mug_shot_url' => 'character_mug_shots/11.webp']);
        $pedestrian->games()->attach($popular);

        $voices = Character::create(['igdb_id' => 12, 'name' => 'Additional Voices', 'slug' => 'additional-voices']);
        $voices->games()->attach([$popular->id, $niche->id]);

        $nicheHero = Character::create(['igdb_id' => 13, 'name' => 'Tiny Hero', 'slug' => 'tiny-hero']);
        $nicheHero->games()->attach($niche);

        $nicheWithMugShot = Character::create(['igdb_id' => 14, 'name' => 'Curated Niche', 'slug' => 'curated-niche', 'mug_shot_igdb_id' => 99, 'mug_shot_url' => 'character_mug_shots/99.webp']);
        $nicheWithMugShot->games()->attach($niche);

        $challenged = Character::create(['igdb_id' => 15, 'name' => 'Guard', 'slug' => 'guard']);
        $challenged->games()->attach($niche);
        DailyChallenge::create(['mode' => GameMode::CHARACTER->value, 'date' => '2026-10-01', 'character_id' => $challenged->id]);

        $this->instance(
            MediaService::class,
            Mockery::mock(MediaService::class, function (MockInterface $mock) {
                $mock->shouldReceive('deleteCharacterMugShot')->once()->with('character_mug_shots/11.webp');
            })
        );

        $this->artisan('characters:prune')
            ->expectsOutputToContain('Kept: 3 | Junk: 2 | Unpopular: 1 | Mug shots deleted: 1 | Errors: 0')
            ->assertExitCode(0);

        $this->assertDatabaseHas('characters', ['id' => $trevor->id]);
        $this->assertDatabaseHas('characters', ['id' => $nicheWithMugShot->id]);
        $this->assertDatabaseHas('characters', ['id' => $challenged->id]);
        $this->assertDatabaseMissing('characters', ['id' => $pedestrian->id]);
        $this->assertDatabaseMissing('characters', ['id' => $voices->id]);
        $this->assertDatabaseMissing('characters', ['id' => $nicheHero->id]);
        $this->assertDatabaseMissing('character_game', ['character_id' => $voices->id]);
    }

    public function test_dry_run_deletes_nothing(): void
    {
        $niche = Game::create(['igdb_id' => 2, 'name' => 'Tiny Game', 'slug' => 'tiny-game', 'popularity_tier' => 'niche']);
        $hero = Character::create(['igdb_id' => 13, 'name' => 'Tiny Hero', 'slug' => 'tiny-hero']);
        $hero->games()->attach($niche);

        $this->artisan('characters:prune', ['--dry-run' => true])
            ->expectsOutputToContain('[DRY RUN] Kept: 0 | Junk: 0 | Unpopular: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('characters', ['id' => $hero->id]);
    }

    public function test_tier_option_widens_what_is_kept(): void
    {
        $notable = Game::create(['igdb_id' => 2, 'name' => 'Mid Game', 'slug' => 'mid-game', 'popularity_tier' => 'notable']);
        $hero = Character::create(['igdb_id' => 13, 'name' => 'Mid Hero', 'slug' => 'mid-hero']);
        $hero->games()->attach($notable);

        $this->artisan('characters:prune', ['--tier' => ['legendary', 'popular', 'notable']])
            ->expectsOutputToContain('Kept: 1 | Junk: 0 | Unpopular: 0')
            ->assertExitCode(0);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\GameMode;
use App\Models\Character;
use App\Models\CharacterGender;
use App\Models\CharacterSpecies;
use App\Models\DailyChallenge;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateDailyChallengesTest extends TestCase
{
    use RefreshDatabase;

    private function seedEligibleGames(): void
    {
        Game::factory()->count(10)->create(['popularity_tier' => 'legendary', 'rating' => 85, 'rating_count' => 2000]);
        Game::factory()->count(10)->create(['popularity_tier' => 'popular', 'rating' => 85, 'rating_count' => 500]);
    }

    public function test_it_only_picks_games_from_the_two_top_tiers(): void
    {
        Game::factory()->count(30)->create(['popularity_tier' => 'notable', 'rating' => 90, 'rating_count' => 300]);
        Game::factory()->count(30)->create(['popularity_tier' => 'niche', 'rating' => 90, 'rating_count' => 50]);
        $legendary = Game::factory()->create(['popularity_tier' => 'legendary']);

        $this->artisan('challenges:generate', ['--days' => 5])->assertSuccessful();

        $this->assertSame(6, DailyChallenge::where('mode', GameMode::CLASSIC->value)->count());
        $this->assertSame([$legendary->id], DailyChallenge::where('mode', GameMode::CLASSIC->value)->pluck('game_id')->unique()->values()->all());
    }

    public function test_it_generates_nothing_when_no_top_tier_game_exists(): void
    {
        Game::factory()->count(5)->create(['popularity_tier' => 'niche']);

        $this->artisan('challenges:generate', ['--days' => 0])->assertSuccessful();

        $this->assertSame(0, DailyChallenge::count());
    }

    public function test_character_challenge_requires_image_attributes_and_a_top_tier_game(): void
    {
        $legendary = Game::factory()->create(['popularity_tier' => 'legendary']);
        $niche = Game::factory()->create(['popularity_tier' => 'niche']);
        $gender = CharacterGender::create(['name' => 'Male']);
        $species = CharacterSpecies::create(['name' => 'Human']);

        $eligible = Character::create(['name' => 'Kratos', 'slug' => 'kratos', 'mug_shot_url' => 'character_mug_shots/1.webp', 'gender_id' => $gender->id, 'species_id' => $species->id]);
        $eligible->games()->attach($legendary);

        $nicheOnly = Character::create(['name' => 'Tiny Hero', 'slug' => 'tiny-hero', 'mug_shot_url' => 'character_mug_shots/2.webp', 'gender_id' => $gender->id, 'species_id' => $species->id]);
        $nicheOnly->games()->attach($niche);

        $noImage = Character::create(['name' => 'Faceless', 'slug' => 'faceless', 'gender_id' => $gender->id, 'species_id' => $species->id]);
        $noImage->games()->attach($legendary);

        $this->artisan('challenges:generate', ['--days' => 4])->assertSuccessful();

        $this->assertSame(5, DailyChallenge::where('mode', GameMode::CHARACTER->value)->count());
        $this->assertSame([$eligible->id], DailyChallenge::where('mode', GameMode::CHARACTER->value)->pluck('character_id')->unique()->values()->all());
    }

    public function test_it_generates_classic_challenge_for_today_and_a_week_ahead(): void
    {
        $this->seedEligibleGames();

        $this->artisan('challenges:generate')->assertSuccessful();

        for ($offset = 0; $offset <= 7; $offset++) {
            $date = today()->addDays($offset)->toDateString();

            $this->assertTrue(
                DailyChallenge::where('mode', GameMode::CLASSIC->value)
                    ->whereDate('date', $date)
                    ->exists(),
                "Expected a CLASSIC challenge for {$date}"
            );
        }

        $this->assertSame(
            8,
            DailyChallenge::where('mode', GameMode::CLASSIC->value)->count()
        );
    }

    public function test_it_is_idempotent_and_does_not_duplicate_existing_days(): void
    {
        $this->seedEligibleGames();

        $this->artisan('challenges:generate')->assertSuccessful();
        $firstRunIds = DailyChallenge::where('mode', GameMode::CLASSIC->value)
            ->orderBy('date')
            ->pluck('game_id', 'date');

        $this->artisan('challenges:generate')->assertSuccessful();

        $this->assertSame(
            8,
            DailyChallenge::where('mode', GameMode::CLASSIC->value)->count()
        );

        $secondRunIds = DailyChallenge::where('mode', GameMode::CLASSIC->value)
            ->orderBy('date')
            ->pluck('game_id', 'date');

        $this->assertEquals($firstRunIds->toArray(), $secondRunIds->toArray());
    }

    public function test_it_backfills_a_missing_day_on_a_later_run(): void
    {
        $this->seedEligibleGames();

        $existing = DailyChallenge::create([
            'mode' => GameMode::CLASSIC->value,
            'date' => today()->addDays(3)->toDateString(),
            'game_id' => Game::first()->id,
        ]);

        $this->artisan('challenges:generate')->assertSuccessful();

        $this->assertSame(
            8,
            DailyChallenge::where('mode', GameMode::CLASSIC->value)->count()
        );

        $this->assertSame(
            $existing->game_id,
            DailyChallenge::where('mode', GameMode::CLASSIC->value)
                ->whereDate('date', today()->addDays(3)->toDateString())
                ->value('game_id')
        );
    }

    public function test_days_option_limits_how_far_ahead_it_generates(): void
    {
        $this->seedEligibleGames();

        $this->artisan('challenges:generate', ['--days' => 2])->assertSuccessful();

        $this->assertSame(
            3,
            DailyChallenge::where('mode', GameMode::CLASSIC->value)->count()
        );
    }
}
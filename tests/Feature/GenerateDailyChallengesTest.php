<?php

namespace Tests\Feature;

use App\Enums\GameMode;
use App\Models\DailyChallenge;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateDailyChallengesTest extends TestCase
{
    use RefreshDatabase;

    private function seedEligibleGames(): void
    {
        Game::factory()->count(20)->create([
            'rating' => 85,
            'rating_count' => 150,
        ]);
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
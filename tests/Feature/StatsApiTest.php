<?php

namespace Tests\Feature;

use App\Enums\GameMode;
use App\Models\DailyChallenge;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\SiteStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_distribution_returns_bins_for_mode_and_date(): void
    {
        $game = Game::factory()->create();
        $challenge = DailyChallenge::create([
            'mode' => GameMode::CLASSIC,
            'date' => '2026-05-05',
            'game_id' => $game->id,
        ]);

        GameSession::create([
            'mode' => GameMode::CLASSIC,
            'session_token' => 'token-1',
            'challenge_id' => $challenge->id,
            'attempts' => 1,
            'completed_at' => now(),
        ]);
        GameSession::create([
            'mode' => GameMode::CLASSIC,
            'session_token' => 'token-2',
            'challenge_id' => $challenge->id,
            'attempts' => 2,
            'completed_at' => now(),
        ]);
        GameSession::create([
            'mode' => GameMode::CLASSIC,
            'session_token' => 'token-3',
            'challenge_id' => $challenge->id,
            'attempts' => 2,
            'completed_at' => now(),
        ]);

        $response = $this->getJson('/api/stats/modes/classic/distribution?date=2026-05-05');

        $response->assertOk()
            ->assertJsonPath('mode', 'classic')
            ->assertJsonPath('date', '2026-05-05')
            ->assertJsonPath('total_players', 3)
            ->assertJsonCount(2, 'bins')
            ->assertJsonPath('bins.0.attempts', 1)
            ->assertJsonPath('bins.0.players', 1)
            ->assertJsonPath('bins.1.attempts', 2)
            ->assertJsonPath('bins.1.players', 2);
    }

    public function test_daily_trend_returns_nulls_for_missing_dates(): void
    {
        $game = Game::factory()->create();
        $challenge = DailyChallenge::create([
            'mode' => GameMode::CLASSIC,
            'date' => '2026-05-01',
            'game_id' => $game->id,
        ]);

        GameSession::create([
            'mode' => GameMode::CLASSIC,
            'session_token' => 'token-10',
            'challenge_id' => $challenge->id,
            'attempts' => 3,
            'completed_at' => now(),
        ]);

        $response = $this->getJson('/api/stats/daily-trend?from=2026-05-01&to=2026-05-03');

        $response->assertOk()
            ->assertJsonPath('from', '2026-05-01')
            ->assertJsonPath('to', '2026-05-03')
            ->assertJsonCount(3, 'points')
            ->assertJsonPath('points.0.classic_avg_attempts', 3)
            ->assertJsonPath('points.1.classic_avg_attempts', null)
            ->assertJsonPath('points.2.classic_avg_attempts', null);
    }

    public function test_solved_today_matches_site_stats(): void
    {
        $today = today()->toDateString();
        $game = Game::factory()->create();
        $character = \App\Models\Character::factory()->create();

        $classic = DailyChallenge::create([
            'mode' => GameMode::CLASSIC,
            'date' => $today,
            'game_id' => $game->id,
        ]);
        $screenshots = DailyChallenge::create([
            'mode' => GameMode::GAME_SCREENSHOTS,
            'date' => $today,
            'game_id' => $game->id,
        ]);
        $characterChallenge = DailyChallenge::create([
            'mode' => GameMode::CHARACTER,
            'date' => $today,
            'character_id' => $character->id,
        ]);

        SiteStats::create(['challenge_id' => $classic->id, 'total_players' => 10]);
        SiteStats::create(['challenge_id' => $screenshots->id, 'total_players' => 7]);
        SiteStats::create(['challenge_id' => $characterChallenge->id, 'total_players' => 5]);

        $response = $this->getJson('/api/stats/solved-today');

        $response->assertOk()
            ->assertJsonPath('date', $today)
            ->assertJsonPath('classic_players', 10)
            ->assertJsonPath('screenshots_players', 7)
            ->assertJsonPath('character_players', 5)
            ->assertJsonPath('total_players', 22);
    }
}

<?php

namespace Tests\Unit;

use App\Enums\GameMode;
use App\Models\DailyChallenge;
use App\Models\Game;
use App\Models\GameSession;
use App\Services\GameComparisonService;
use App\Services\GuessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_real_attempts_count_for_win(): void
    {
        $targetGame = Game::factory()->create();
        $wrongGame = Game::factory()->create();
        $challengeDate = '2026-01-15';

        $challenge = DailyChallenge::create([
            'mode' => GameMode::CLASSIC,
            'date' => $challengeDate,
            'game_id' => $targetGame->id,
        ]);

        $service = new GuessService(new GameComparisonService);
        $sessionToken = 'test-session-token';

        $this->assertDatabaseHas('daily_challenges', [
            'id' => $challenge->id,
            'mode' => GameMode::CLASSIC->value,
            'game_id' => $targetGame->id,
        ]);

        $firstResult = $service->makeGuess(
            $wrongGame->id,
            GameMode::CLASSIC,
            $sessionToken,
            1,
            $challengeDate
        );

        $this->assertTrue($firstResult['success']);
        $this->assertFalse($firstResult['is_correct']);
        $this->assertDatabaseCount('game_sessions', 0);

        $secondResult = $service->makeGuess(
            $targetGame->id,
            GameMode::CLASSIC,
            $sessionToken,
            2,
            $challengeDate
        );

        $this->assertTrue($secondResult['success']);
        $this->assertTrue($secondResult['is_correct']);
        $this->assertSame(2, $secondResult['attempts']);

        $session = GameSession::query()->first();

        $this->assertNotNull($session);
        $this->assertSame(2, $session->attempts);
    }
}

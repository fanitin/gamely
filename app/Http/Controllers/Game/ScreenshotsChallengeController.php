<?php

namespace App\Http\Controllers\Game;

use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Models\DailyChallenge;
use Illuminate\Http\JsonResponse;

class ScreenshotsChallengeController extends Controller
{
    public function today(): JsonResponse
    {
        $challenge = DailyChallenge::forMode(GameMode::GAME_SCREENSHOTS)
            ->forDate(today()->toDateString())
            ->with([
                'game.screenshots',
                'game.genres',
                'game.developers',
                'game.publishers',
                'game.franchises',
                'game.collections',
            ])
            ->first();

        if (!$challenge || !$challenge->game) {
            return response()->json([
                'success' => false,
                'error' => 'No challenge available for today',
            ], 404);
        }

        $game = $challenge->game;

        $screenshots = $game->screenshots()
            ->orderBy('order')
            ->limit(8)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'url' => $s->url,
            ])
            ->filter(fn ($s) => $s['url'] !== null)
            ->values();

        if ($screenshots->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'No screenshots available for this challenge',
            ], 404);
        }

        $hints = [
            'year' => [
                'value' => $game->release_date?->year,
                'unlock_at' => 4,
            ],
            'genre' => [
                'value' => $game->genres->pluck('name')->join(', ') ?: null,
                'unlock_at' => 8,
            ],
            'first_letter' => [
                'value' => mb_strtoupper(mb_substr($game->name, 0, 1)),
                'unlock_at' => 12,
            ],
        ];

        return response()->json([
            'success' => true,
            'challenge_id' => $challenge->id,
            'screenshots' => $screenshots,
            'total_screenshots' => $screenshots->count(),
            'hints' => $hints,
        ]);
    }
}

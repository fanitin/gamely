<?php

namespace App\Http\Controllers\Game;

use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Models\DailyChallenge;
use App\Services\HintService;
use Illuminate\Http\JsonResponse;

class ChallengeController extends Controller
{
    public function __construct(
        private HintService $hintService
    ) {}

    public function classic(): JsonResponse
    {
        $challenge = DailyChallenge::forMode(GameMode::CLASSIC)
            ->forDate(today()->toDateString())
            ->with([
                'game.genres',
                'game.platforms',
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
        $hints = $this->hintService->getHintsForMode(GameMode::CLASSIC, $game);

        return response()->json([
            'success' => true,
            'challenge_id' => $challenge->id,
            'hints' => $hints,
        ]);
    }

    public function screenshots(): JsonResponse
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

        $hints = $this->hintService->getHintsForMode(GameMode::GAME_SCREENSHOTS, $game);

        return response()->json([
            'success' => true,
            'challenge_id' => $challenge->id,
            'screenshots' => $screenshots,
            'total_screenshots' => $screenshots->count(),
            'hints' => $hints,
        ]);
    }

    public function characterAttributes(): JsonResponse
    {
        $challenge = DailyChallenge::forMode(GameMode::CHARACTER_ATTRIBUTES)
            ->forDate(today()->toDateString())
            ->with([
                'character.games.franchises',
                'character.games.collections',
                'character.gender',
                'character.species',
            ])
            ->first();

        if (!$challenge || !$challenge->character) {
            return response()->json([
                'success' => false,
                'error' => 'No challenge available for today',
            ], 404);
        }

        $character = $challenge->character;
        $hints = $this->hintService->getHintsForMode(GameMode::CHARACTER_ATTRIBUTES, $character);

        return response()->json([
            'success' => true,
            'challenge_id' => $challenge->id,
            'hints' => $hints,
        ]);
    }

    public function characterImage(): JsonResponse
    {
        $challenge = DailyChallenge::forMode(GameMode::CHARACTER_IMAGE)
            ->forDate(today()->toDateString())
            ->with([
                'character.games',
                'character.gender',
                'character.species',
            ])
            ->first();

        if (!$challenge || !$challenge->character) {
            return response()->json([
                'success' => false,
                'error' => 'No challenge available for today',
            ], 404);
        }

        $character = $challenge->character;
        $hints = $this->hintService->getHintsForMode(GameMode::CHARACTER_IMAGE, $character);

        return response()->json([
            'success' => true,
            'challenge_id' => $challenge->id,
            'character_image' => $character->mug_shot_url,
            'hints' => $hints,
        ]);
    }
}

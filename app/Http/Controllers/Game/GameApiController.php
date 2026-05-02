<?php

namespace App\Http\Controllers\Game;

use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Services\GameSearchService;
use App\Services\GuessService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class GameApiController extends Controller
{
    public function __construct(
        private GameSearchService $gameSearchService,
        private GuessService      $guessService
    )
    {
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:64'],
            'type' => ['sometimes', 'string', Rule::in(['game', 'character'])],
        ]);

        $query = $validated['query'];
        $type = $validated['type'] ?? 'game';

        $results = $this->gameSearchService->search($query, $type);
        return response()->json($results);
    }

    public function guess(Request $request)
    {
        $validated = $request->validate([
            'entity_id' => ['required', 'integer', 'min:1'],
            'mode' => ['required', 'string', Rule::in(array_map(fn (GameMode $mode) => $mode->value, GameMode::cases()))],
        ]);

        $entityId = (int) $validated['entity_id'];
        $mode = GameMode::tryFrom($validated['mode']);
        if (!$mode) {
            return response()->json(['error' => 'Invalid game mode'], 400);
        }

        $sessionToken = $request->cookie('session_token');
        if (!$sessionToken) {
            $sessionToken = (string)Str::uuid();
            cookie()->queue(cookie(
                name: 'session_token',
                value: $sessionToken,
                minutes: 60 * 24 * 365,
                path: '/',
                domain: null,
                secure: $request->isSecure(),
                httpOnly: true,
                raw: false,
                sameSite: 'lax',
            ));
        }

        $result = $this->guessService->makeGuess($entityId, $mode, $sessionToken);

        return response()->json($result);
    }
}

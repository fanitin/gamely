<?php

namespace App\Http\Controllers\Game;

use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Services\GameSearchService;
use App\Services\GuessService;
use Illuminate\Http\Request;
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
        $query = $request->input('query', '');
        $type  = $request->input('type', 'game');

        if (empty($query)) {
            return response()->json(['error' => 'Query parameter is required'], 400);
        }

        $results = $this->gameSearchService->search($query, $type);
        return response()->json($results);
    }

    public function guess(Request $request)
    {
        $entityId  = $request->input('entity_id');
        $modeValue = $request->input('mode');

        if (empty($entityId) || empty($modeValue)) {
            return response()->json(['error' => 'Entity ID and mode are required'], 400);
        }

        $mode = GameMode::tryFrom($modeValue);
        if (!$mode) {
            return response()->json(['error' => 'Invalid game mode'], 400);
        }

        $sessionToken = $request->cookie('session_token');
        if (!$sessionToken) {
            $sessionToken = (string)Str::uuid();
            cookie()->queue('session_token', $sessionToken, 60 * 24 * 365);
        }

        $result = $this->guessService->makeGuess($entityId, $mode, $sessionToken);

        return response()->json($result);
    }
}

<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\GameSearchService;
use App\Services\GuessService;
use Illuminate\Http\Request;

class GameApiController extends Controller
{
    public function __construct(
        private GameSearchService $gameSearchService,
        private GuessService $guessService
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
        $entityId = $request->input('entity_id');
        $mode     = $request->input('mode');

        if (empty($entityId)) {
            return response()->json(['error' => 'Entity ID is required'], 400);
        }

        $guess = $this->guessService->makeGuess($entityId, $mode);
    }
}

<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\GameSearchService;
use Illuminate\Http\Request;

class GameApiController extends Controller
{
    public function __construct(
        private GameSearchService $gameSearchService,
    ){}

    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $type = $request->input('type', 'game');

        if (empty($query)) {
            return response()->json(['error' => 'Query parameter is required'], 400);
        }

        $results = $this->gameSearchService->search($query, $type);
        return response()->json($results);
    }
}

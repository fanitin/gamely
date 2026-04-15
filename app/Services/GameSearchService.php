<?php

namespace App\Services;

use App\Models\Game;

class GameSearchService
{
    public function search(string $query, string $type)
    {
        if ($type === "game") {
            $query = trim($query);

            if (empty($query)) {
                return collect([]);
            }

            $queryLower = strtolower($query);

            $exact = Game::whereRaw('LOWER(name) = ?', [$queryLower])
                ->selectRaw('*, 1 as relevance')
                ->limit(50);

            $startsWith = Game::whereRaw('LOWER(name) LIKE ?', [$queryLower . '%'])
                ->whereRaw('LOWER(name) != ?', [$queryLower])
                ->selectRaw('*, 2 as relevance')
                ->limit(50);

            $wholeWord = Game::whereRaw('LOWER(name) LIKE ?', ['% ' . $queryLower . '%'])
                ->selectRaw('*, 3 as relevance')
                ->limit(50);

            $partial = Game::whereRaw('LOWER(name) LIKE ?', ['%' . $queryLower . '%'])
                ->whereRaw('LOWER(name) NOT LIKE ?', [$queryLower . '%'])
                ->whereRaw('LOWER(name) NOT LIKE ?', ['% ' . $queryLower . '%'])
                ->selectRaw('*, 4 as relevance')
                ->limit(50);

            $games = $exact
                ->union($startsWith)
                ->union($wholeWord)
                ->union($partial)
                ->orderBy('relevance')
                ->orderBy('rating', 'desc')
                ->limit(100)
                ->get()
                ->makeHidden(['relevance']);

            return $games;
        }
    }
}

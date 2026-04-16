<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Game;

class GameSearchService
{
    public function search(string $query, string $type)
    {
        if (empty($query) || empty($type)) {
            return collect([]);
        }

        $query = strtolower(trim($query));

        return match ($type) {
            'game' => $this->searchGames($query),
            'character' => $this->searchCharacters($query),
            default => collect([]),
        };
    }

    private function searchGames(string $query)
    {
        $exact = Game::whereRaw('LOWER(name) = ?', [$query])
            ->selectRaw('*, 1 as relevance')
            ->limit(50);

        $startsWith = Game::whereRaw('LOWER(name) LIKE ?', [$query . '%'])
            ->whereRaw('LOWER(name) != ?', [$query])
            ->selectRaw('*, 2 as relevance')
            ->limit(50);

        $wholeWord = Game::whereRaw('LOWER(name) LIKE ?', ['% ' . $query . '%'])
            ->selectRaw('*, 3 as relevance')
            ->limit(50);

        $partial = Game::whereRaw('LOWER(name) LIKE ?', ['%' . $query . '%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', [$query . '%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['% ' . $query . '%'])
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

    private function searchCharacters(string $query)
    {
        $exact = Character::whereRaw('LOWER(name) = ?', [$query])
            ->selectRaw('*, 1 as relevance')
            ->limit(50);

        $startsWith = Character::whereRaw('LOWER(name) LIKE ?', [$query . '%'])
            ->whereRaw('LOWER(name) != ?', [$query])
            ->selectRaw('*, 2 as relevance')
            ->limit(50);

        $wholeWord = Character::whereRaw('LOWER(name) LIKE ?', ['% ' . $query . '%'])
            ->selectRaw('*, 3 as relevance')
            ->limit(50);

        $partial = Character::whereRaw('LOWER(name) LIKE ?', ['%' . $query . '%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', [$query . '%'])
            ->whereRaw('LOWER(name) NOT LIKE ?', ['% ' . $query . '%'])
            ->selectRaw('*, 4 as relevance')
            ->limit(50);

        $characters = $exact
            ->union($startsWith)
            ->union($wholeWord)
            ->union($partial)
            ->orderBy('relevance')
            ->limit(100)
            ->get()
            ->makeHidden(['relevance']);

        return $characters;
    }
}

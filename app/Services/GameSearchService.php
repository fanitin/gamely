<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Game;
use Illuminate\Support\Facades\Storage;

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
        $games = Game::whereRaw('LOWER(name) LIKE ?', ['%'.$query.'%'])
            ->selectRaw('*, (CASE 
                WHEN LOWER(name) = ? THEN 1
                WHEN LOWER(name) LIKE ? THEN 2
                WHEN LOWER(name) LIKE ? THEN 3
                ELSE 4
            END) as relevance', [
                $query,
                $query.'%',
                '% '.$query.'%',
            ])
            ->orderBy('relevance')
            ->orderBy('rating', 'desc')
            ->limit(100)
            ->get();

        return $games->map(function ($game) {
            return [
                'id' => $game->id,
                'name' => $game->name,
                'display_name' => $game->display_name,
                'image' => $game->cover_url,
                'meta' => $game->release_date ? $game->release_date->format('Y') : null,
            ];
        });
    }

    private function searchCharacters(string $query)
    {
        $characters = Character::whereRaw('LOWER(name) LIKE ?', ['%'.$query.'%'])
            ->selectRaw('*, (CASE 
                WHEN LOWER(name) = ? THEN 1
                WHEN LOWER(name) LIKE ? THEN 2
                WHEN LOWER(name) LIKE ? THEN 3
                ELSE 4
            END) as relevance', [
                $query,
                $query.'%',
                '% '.$query.'%',
            ])
            ->orderBy('relevance')
            ->limit(100)
            ->with(['games.franchises', 'games.collections'])
            ->get();

        return $characters->map(function ($character) {
            $metaParts = [];
            
            if ($character->relationLoaded('games')) {
                $franchises = $character->games->flatMap->franchises->unique('id')->pluck('name');
                $collections = $character->games->flatMap->collections->unique('id')->pluck('name');
                
                if ($franchises->isNotEmpty()) {
                    $metaParts[] = $franchises->join(', ');
                }
                if ($collections->isNotEmpty()) {
                    $metaParts[] = $collections->join(', ');
                }
            }

            return [
                'id' => $character->id,
                'name' => $character->name,
                'display_name' => $character->name,
                'image' => $character->mug_shot_url ? Storage::disk('r2')->url($character->mug_shot_url) : null,
                'meta' => !empty($metaParts) ? implode(' | ', array_unique($metaParts)) : null,
            ];
        });
    }
}

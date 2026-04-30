<?php

namespace App\Services;

use App\Enums\GameMode;
use App\Models\Game;
use App\Models\Character;

class HintService
{
    public function getHintsForMode(GameMode $mode, Game|Character $entity): array
    {
        return match ($mode) {
            GameMode::CLASSIC => $this->getClassicHints($entity),
            GameMode::GAME_SCREENSHOTS => $this->getScreenshotsHints($entity),
            GameMode::CHARACTER_ATTRIBUTES => $this->getCharacterAttributesHints($entity),
            GameMode::CHARACTER_IMAGE => $this->getCharacterImageHints($entity),
        };
    }

    private function getClassicHints(Game $game): array
    {
        return [
            'platforms' => [
                'value' => $game->platforms->pluck('name')->join(', ') ?: null,
                'unlock_at' => 5,
            ],
            'first_letter' => [
                'value' => mb_strtoupper(mb_substr($game->name, 0, 1)),
                'unlock_at' => 10,
            ],
            'companies' => [
                'value' => collect([
                    $game->developers->pluck('name')->join(', '),
                    $game->publishers->pluck('name')->join(', ')
                ])->filter()->join(' / ') ?: null,
                'unlock_at' => 15,
            ],
        ];
    }

    private function getScreenshotsHints(Game $game): array
    {
        return [
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
    }

    private function getCharacterAttributesHints(Character $character): array
    {
        return [
            'first_letter' => [
                'value' => mb_strtoupper(mb_substr($character->name, 0, 1)),
                'unlock_at' => 5,
            ],
            'gender' => [
                'value' => $character->gender?->name,
                'unlock_at' => 10,
            ],
            'species' => [
                'value' => $character->species?->name,
                'unlock_at' => 15,
            ],
        ];
    }

    private function getCharacterImageHints(Character $character): array
    {
        return [
            'gender' => [
                'value' => $character->gender?->name,
                'unlock_at' => 4,
            ],
            'species' => [
                'value' => $character->species?->name,
                'unlock_at' => 8,
            ],
            'first_letter' => [
                'value' => mb_strtoupper(mb_substr($character->name, 0, 1)),
                'unlock_at' => 12,
            ],
        ];
    }
}

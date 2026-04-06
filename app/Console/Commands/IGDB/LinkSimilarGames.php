<?php

namespace App\Console\Commands\IGDB;

use App\Models\Game;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-similar-games')]
#[Description('Link similar games for existing games in DB')]
class LinkSimilarGames extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'games';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, similar_games; where similar_games != null;';
    }

    protected function processItem(array $item): string
    {
        $game = Game::where('igdb_id', $item['id'])->first();

        if (! $game) {
            return 'skipped';
        }

        if (empty($item['similar_games'])) {
            return 'skipped';
        }

        $similarGameIds = Game::whereIn('igdb_id', $item['similar_games'])->pluck('id');

        if ($similarGameIds->isEmpty()) {
            return 'skipped';
        }

        $game->similarGames()->syncWithoutDetaching($similarGameIds);

        return 'updated';
    }
}

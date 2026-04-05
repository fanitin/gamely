<?php

namespace App\Console\Commands\IGDB;

use App\Models\GameMode;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-game-modes')]
#[Description('Import game modes from IGDB API')]
class ImportGameModes extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'game_modes';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug;';
    }

    protected function processItem(array $item): string
    {
        $family = GameMode::updateOrCreate(
            ['igdb_id' => $item['id']],
            [
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
            ]
        );

        if ($family->wasRecentlyCreated) {
            return 'created';
        }

        if ($family->wasChanged()) {
            return 'updated';
        }

        return 'skipped';
    }
}

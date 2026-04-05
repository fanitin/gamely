<?php

namespace App\Console\Commands\IGDB;

use App\Models\PlayerPerspective;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-player-perspectives')]
#[Description('Import player perspectives from IGDB API')]
class ImportPlayerPerspective extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'player_perspectives';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug;';
    }

    protected function processItem(array $item): string
    {
        $family = PlayerPerspective::updateOrCreate(
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

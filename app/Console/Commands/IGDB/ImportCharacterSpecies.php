<?php

namespace App\Console\Commands\IGDB;

use App\Models\CharacterSpecies;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-character-species')]
#[Description('Import character species from IGDB API')]
class ImportCharacterSpecies extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'character_species';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name;';
    }

    protected function processItem(array $item): string
    {
        $species = CharacterSpecies::updateOrCreate(
            ['igdb_id' => $item['id']],
            ['name' => $item['name'] ?? 'Unknown']
        );

        if ($species->wasRecentlyCreated) {
            return 'created';
        }

        if ($species->wasChanged()) {
            return 'updated';
        }

        return 'skipped';
    }
}

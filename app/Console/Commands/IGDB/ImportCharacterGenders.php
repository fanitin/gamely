<?php

namespace App\Console\Commands\IGDB;

use App\Models\CharacterGender;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-character-genders')]
#[Description('Import character genders from IGDB API')]
class ImportCharacterGenders extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'character_genders';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name;';
    }

    protected function processItem(array $item): string
    {
        $gender = CharacterGender::updateOrCreate(
            ['igdb_id' => $item['id']],
            ['name' => $item['name'] ?? 'Unknown']
        );

        if ($gender->wasRecentlyCreated) {
            return 'created';
        }

        if ($gender->wasChanged()) {
            return 'updated';
        }

        return 'skipped';
    }
}

<?php

namespace App\Console\Commands\IGDB;

use App\Models\Genre;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-genres')]
#[Description('Import genres from IGDB API')]
class ImportGenres extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'genres';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug;';
    }

    protected function processItem(array $item): string
    {
        $family = Genre::updateOrCreate(
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

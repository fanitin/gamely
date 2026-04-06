<?php

namespace App\Console\Commands\IGDB;

use App\Models\Collection;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-collections')]
#[Description('Import collections from IGDB API')]
class ImportCollections extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'collections';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug;';
    }

    protected function processItem(array $item): string
    {
        $collection = Collection::updateOrCreate(
            ['igdb_id' => $item['id']],
            [
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
            ]
        );

        if ($collection->wasRecentlyCreated) {
            return 'created';
        }

        if ($collection->wasChanged()) {
            return 'updated';
        }

        return 'skipped';
    }
}

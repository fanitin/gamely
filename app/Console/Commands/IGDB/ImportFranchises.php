<?php

namespace App\Console\Commands\IGDB;

use App\Models\Franchise;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-franchises')]
#[Description('Import franchises from IGDB API')]
class ImportFranchises extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'franchises';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug;';
    }

    protected function processItem(array $item): string
    {
        $franchise = Franchise::updateOrCreate(
            ['igdb_id' => $item['id']],
            [
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
            ]
        );

        if ($franchise->wasRecentlyCreated) {
            return 'created';
        }

        if ($franchise->wasChanged()) {
            return 'updated';
        }

        return 'skipped';
    }
}

<?php

namespace App\Console\Commands\IGDB;

use App\Models\Theme;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-themes')]
#[Description('Import themes from IGDB API')]
class ImportTheme extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'themes';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug;';
    }

    protected function processItem(array $item): string
    {
        $family = Theme::updateOrCreate(
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

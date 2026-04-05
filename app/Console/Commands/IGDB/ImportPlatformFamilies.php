<?php

namespace App\Console\Commands\IGDB;

use App\Models\PlatformFamily;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-platform-families')]
#[Description('Import platform families from IGDB API')]
class ImportPlatformFamilies extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'platform_families';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug;';
    }

    protected function processItem(array $item): string
    {
        $family = PlatformFamily::updateOrCreate(
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

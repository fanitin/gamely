<?php

namespace App\Console\Commands\IGDB;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:platform-families')]
#[Description('Import platform families from IGDB')]
class ImportPlatformFamilies extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'platform_families';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug, checksum;';
    }

    protected function processItem(array $item): string
    {
        $family = \App\Models\PlatformFamily::where('igdb_id', $item['id'])->first();

        if ($family) {
            $family->update([
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
            ]);
            return 'updated';
        }

        \App\Models\PlatformFamily::create([
            'igdb_id' => $item['id'],
            'name'    => $item['name'] ?? null,
            'slug'    => $item['slug'] ?? null,
        ]);

        return 'created';
    }
}

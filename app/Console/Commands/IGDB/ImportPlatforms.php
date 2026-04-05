<?php

namespace App\Console\Commands\IGDB;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:platforms')]
#[Description('Import platforms from IGDB')]
class ImportPlatforms extends AbstractIgdbImport
{
    protected function getEndpoint(): string
    {
        return 'platforms';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug, checksum;';
    }

    protected function processItem(array $item): string
    {
        $platform = \App\Models\Platform::where('igdb_id', $item['id'])->first();

        if ($platform) {
            $platform->update([
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
            ]);
            return 'updated';
        }

        \App\Models\Platform::create([
            'igdb_id' => $item['id'],
            'name'    => $item['name'] ?? null,
            'slug'    => $item['slug'] ?? null,
        ]);

        return 'created';
    }
}

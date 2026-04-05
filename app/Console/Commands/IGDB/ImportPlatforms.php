<?php

namespace App\Console\Commands\IGDB;

use App\Models\Platform;
use App\Models\PlatformFamily;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-platforms')]
#[Description('Import platforms from IGDB API')]
class ImportPlatforms extends AbstractIgdbImport
{
    protected array $familyCache = [];

    protected function getEndpoint(): string
    {
        return 'platforms';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug, abbreviation, platform_family, platform_logo.id, platform_logo.image_id;';
    }

    protected function processItem(array $item): string
    {
        $familyId = null;

        if (! empty($item['platform_family'])) {
            $igdbFamilyId = $item['platform_family'];

            if (! array_key_exists($igdbFamilyId, $this->familyCache)) {
                $this->familyCache[$igdbFamilyId] = PlatformFamily::where('igdb_id', $igdbFamilyId)->value('id');
            }

            $familyId = $this->familyCache[$igdbFamilyId];
        }

        $logoId = null;
        $logoUrl = null;

        if (! empty($item['platform_logo']) && is_array($item['platform_logo'])) {
            $logoId = $item['platform_logo']['id'] ?? null;
            $imageId = $item['platform_logo']['image_id'] ?? null;

            if ($imageId) {
                $logoUrl = "https://images.igdb.com/igdb/image/upload/t_logo_med/{$imageId}.jpg";
            }
        }

        $platform = Platform::updateOrCreate(
            ['igdb_id' => $item['id']],
            [
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
                'abbreviation' => $item['abbreviation'] ?? null,
                'platform_family_id' => $familyId,
                'platform_logo_id' => $logoId,
                'url' => $logoUrl,
            ]
        );

        if ($platform->wasRecentlyCreated) {
            return 'created';
        }

        if ($platform->wasChanged()) {
            return 'updated';
        }

        return 'skipped';
    }
}

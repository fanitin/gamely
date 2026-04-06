<?php

namespace App\Console\Commands\IGDB;

use App\Models\Collection;
use App\Models\Company;
use App\Models\Franchise;
use App\Models\Game;
use App\Models\GameMode;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\PlayerPerspective;
use App\Models\Theme;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Carbon;

#[Signature('import:igdb-games')]
#[Description('Import games from IGDB API')]
class ImportGames extends AbstractIgdbImport
{
    protected static ?array $cache = [];

    protected function getEndpoint(): string
    {
        return 'games';
    }

    protected function getQueryBody(): string
    {
        $fields = [
            'id', 'name', 'slug', 'summary', 'storyline', 'first_release_date',
            'rating', 'rating_count', 'game_type', 'cover',
            'collections', 'franchises', 'genres', 'platforms', 'themes', 'game_modes', 'player_perspectives',
            'involved_companies.developer', 'involved_companies.publisher',
            'involved_companies.company.name', 'involved_companies.company.slug', 'involved_companies.company.id',
            'involved_companies.company.logo.image_id',
        ];

        return 'fields '.implode(', ', $fields).'; where game_type = (0,8,9) & rating_count >= 20 & rating > 50 & cover != null & screenshots != null & first_release_date != null;';
    }

    protected function processItem(array $item): string
    {
        $date = null;
        if (! empty($item['first_release_date'])) {
            $date = Carbon::createFromTimestamp($item['first_release_date'])->toDateString();
        }

        $game = Game::updateOrCreate(
            ['igdb_id' => $item['id']],
            [
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
                'summary' => $item['summary'] ?? null,
                'storyline' => $item['storyline'] ?? null,
                'release_date' => $date,
                'rating' => $item['rating'] ?? null,
                'rating_count' => $item['rating_count'] ?? null,
                'game_type' => $item['game_type'] ?? 0,
                'cover_igdb_id' => $item['cover'] ?? null,
            ]
        );

        if ($game->wasRecentlyCreated) {
            $this->syncRelations($game, $item);
            $this->syncCompanies($game, $item['involved_companies'] ?? []);

            return 'created';
        }

        $wasUpdated = $game->wasChanged();

        if ($this->syncRelations($game, $item)) {
            $wasUpdated = true;
        }

        if ($this->syncCompanies($game, $item['involved_companies'] ?? [])) {
            $wasUpdated = true;
        }

        return $wasUpdated ? 'updated' : 'skipped';
    }

    private function syncRelations(Game $game, array $item): bool
    {
        $hasChanges = false;

        $relations = [
            'genres' => [Genre::class, $item['genres'] ?? []],
            'platforms' => [Platform::class, $item['platforms'] ?? []],
            'themes' => [Theme::class, $item['themes'] ?? []],
            'gameModes' => [GameMode::class, $item['game_modes'] ?? []],
            'playerPerspectives' => [PlayerPerspective::class, $item['player_perspectives'] ?? []],
            'collections' => [Collection::class, $item['collections'] ?? []],
            'franchises' => [Franchise::class, $item['franchises'] ?? []],
        ];

        foreach ($relations as $relation => $config) {
            if ($this->syncRelation($game, $relation, $config[0], $config[1])) {
                $hasChanges = true;
            }
        }

        return $hasChanges;
    }

    private function syncCompanies(Game $game, array $involvedCompanies): bool
    {
        if (empty($involvedCompanies)) {
            return false;
        }

        $developerIds = [];
        $publisherIds = [];

        foreach ($involvedCompanies as $involved) {
            if (empty($involved['company'])) {
                continue;
            }

            $companyData = $involved['company'];
            $company = $this->getOrCreateCompany($companyData);

            if ($involved['developer'] ?? false) {
                $developerIds[] = $company->id;
            }
            if ($involved['publisher'] ?? false) {
                $publisherIds[] = $company->id;
            }
        }

        $devChanges = $game->developers()->sync($developerIds);
        $pubChanges = $game->publishers()->sync($publisherIds);

        return ! empty($devChanges['attached']) || ! empty($devChanges['detached']) || ! empty($devChanges['updated']) ||
               ! empty($pubChanges['attached']) || ! empty($pubChanges['detached']) || ! empty($pubChanges['updated']);
    }

    private function getOrCreateCompany(array $data): Company
    {
        $igdbId = $data['id'];

        if (! isset(self::$cache['companies'])) {
            self::$cache['companies'] = [];
        }

        if (! isset(self::$cache['companies'][$igdbId])) {
            $logoUrl = null;
            if (! empty($data['logo']['image_id'])) {
                $imageId = $data['logo']['image_id'];
                $logoUrl = "https://images.igdb.com/igdb/image/upload/t_logo_med/{$imageId}.jpg";
            }

            $company = Company::updateOrCreate(
                ['igdb_id' => $igdbId],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'logo_url' => $logoUrl,
                ]
            );
            self::$cache['companies'][$igdbId] = $company;
        }

        return self::$cache['companies'][$igdbId];
    }

    private function syncRelation(Game $game, string $relation, string $modelClass, array $igdbIds): bool
    {
        if (empty($igdbIds)) {
            return false;
        }

        if (! isset(self::$cache[$modelClass])) {
            self::$cache[$modelClass] = $modelClass::pluck('id', 'igdb_id')->toArray();
        }

        $localIds = [];
        foreach ($igdbIds as $igdbId) {
            if (isset(self::$cache[$modelClass][$igdbId])) {
                $localIds[] = self::$cache[$modelClass][$igdbId];
            }
        }

        $changes = $game->$relation()->sync($localIds);

        return ! empty($changes['attached']) || ! empty($changes['detached']) || ! empty($changes['updated']);
    }
}

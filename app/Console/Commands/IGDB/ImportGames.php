<?php

namespace App\Console\Commands\IGDB;

use App\Models\Collection;
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
    protected function getEndpoint(): string
    {
        return 'games';
    }

    protected function getQueryBody(): string
    {
        return 'fields id, name, slug, summary, first_release_date, rating, rating_count, game_type, collections, franchises, cover, genres, platforms, themes, game_modes, player_perspectives; where game_type = (0,8,9) & rating_count >= 20 & rating > 50 & cover != null & screenshots != null & first_release_date != null;';
    }

    protected function processItem(array $item): string
    {
        $year = null;
        if (! empty($item['first_release_date'])) {
            $year = Carbon::createFromTimestamp($item['first_release_date'])->year;
        }

        $game = Game::updateOrCreate(
            ['igdb_id' => $item['id']],
            [
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
                'summary' => $item['summary'] ?? null,
                'release_year' => $year,
                'rating' => $item['rating'] ?? null,
                'rating_count' => $item['rating_count'] ?? null,
                'game_type' => $item['game_type'] ?? 0,
                'cover_igdb_id' => $item['cover'] ?? null,
            ]
        );

        $wasCreated = $game->wasRecentlyCreated;

        if (! empty($item['collections'])) {
            $collectionIds = Collection::whereIn('igdb_id', $item['collections'])->pluck('id');
            $game->collections()->sync($collectionIds);
        }

        if (! empty($item['genres'])) {
            $genreIds = Genre::whereIn('igdb_id', $item['genres'])->pluck('id');
            $game->genres()->sync($genreIds);
        }

        if (! empty($item['platforms'])) {
            $platformIds = Platform::whereIn('igdb_id', $item['platforms'])->pluck('id');
            $game->platforms()->sync($platformIds);
        }

        if (! empty($item['themes'])) {
            $themeIds = Theme::whereIn('igdb_id', $item['themes'])->pluck('id');
            $game->themes()->sync($themeIds);
        }

        if (! empty($item['game_modes'])) {
            $modeIds = GameMode::whereIn('igdb_id', $item['game_modes'])->pluck('id');
            $game->gameModes()->sync($modeIds);
        }

        if (! empty($item['player_perspectives'])) {
            $perspectiveIds = PlayerPerspective::whereIn('igdb_id', $item['player_perspectives'])->pluck('id');
            $game->playerPerspectives()->sync($perspectiveIds);
        }

        if (! empty($item['franchises'])) {
            $franchiseIds = Franchise::whereIn('igdb_id', $item['franchises'])->pluck('id');
            $game->franchises()->sync($franchiseIds);
        }

        return $wasCreated ? 'created' : 'updated';
    }
}

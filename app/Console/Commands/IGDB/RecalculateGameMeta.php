<?php

namespace App\Console\Commands\IGDB;

use App\Enums\PopularityTier;
use App\Models\Game;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('games:recalculate-meta')]
#[Description('Recalculate franchise_start_year and popularity_tier for all games')]
class RecalculateGameMeta extends Command
{
    public function handle(): int
    {
        $this->info('Recalculating game metadata...');

        $franchiseYears = $this->buildFranchiseYearMap();
        $collectionYears = $this->buildCollectionYearMap();

        $stats = ['updated' => 0, 'skipped' => 0];

        Game::query()
            ->with(['franchises', 'collections'])
            ->chunkById(500, function ($games) use ($franchiseYears, $collectionYears, &$stats) {
                foreach ($games as $game) {
                    $changed = false;

                    $franchiseStartYear = $this->resolveFranchiseStartYear(
                        $game,
                        $franchiseYears,
                        $collectionYears
                    );

                    if ($game->franchise_start_year !== $franchiseStartYear) {
                        $game->franchise_start_year = $franchiseStartYear;
                        $changed = true;
                    }

                    $newTier = PopularityTier::fromRatingCount(
                        $game->rating_count ?? 0,
                        $game->hypes ?? 0
                    );

                    if ($game->popularity_tier !== $newTier) {
                        $game->popularity_tier = $newTier;
                        $changed = true;
                    }

                    if ($changed) {
                        $game->save();
                        $stats['updated']++;
                    } else {
                        $stats['skipped']++;
                    }
                }

                $this->info(sprintf(
                    'Progress - Updated: %d | Skipped: %d',
                    $stats['updated'],
                    $stats['skipped']
                ));
            });

        $this->info(sprintf(
            'Done! Updated: %d | Skipped: %d',
            $stats['updated'],
            $stats['skipped']
        ));

        return self::SUCCESS;
    }

    private function buildFranchiseYearMap(): array
    {
        $map = [];

        $rows = \DB::table('game_franchise')
            ->join('games', 'games.id', '=', 'game_franchise.game_id')
            ->whereNotNull('games.release_date')
            ->selectRaw('game_franchise.franchise_id, MIN(YEAR(games.release_date)) as min_year')
            ->groupBy('game_franchise.franchise_id')
            ->get();

        foreach ($rows as $row) {
            $map[$row->franchise_id] = (int) $row->min_year;
        }

        return $map;
    }

    private function buildCollectionYearMap(): array
    {
        $map = [];

        $rows = \DB::table('game_collection')
            ->join('games', 'games.id', '=', 'game_collection.game_id')
            ->whereNotNull('games.release_date')
            ->selectRaw('game_collection.collection_id, MIN(YEAR(games.release_date)) as min_year')
            ->groupBy('game_collection.collection_id')
            ->get();

        foreach ($rows as $row) {
            $map[$row->collection_id] = (int) $row->min_year;
        }

        return $map;
    }

    private function resolveFranchiseStartYear(Game $game, array $franchiseYears, array $collectionYears): ?int
    {
        $years = [];

        foreach ($game->franchises as $franchise) {
            if (isset($franchiseYears[$franchise->id])) {
                $years[] = $franchiseYears[$franchise->id];
            }
        }

        foreach ($game->collections as $collection) {
            if (isset($collectionYears[$collection->id])) {
                $years[] = $collectionYears[$collection->id];
            }
        }

        if (empty($years)) {
            return $game->release_date
                ? (int) $game->release_date->format('Y')
                : null;
        }

        return min($years);
    }
}

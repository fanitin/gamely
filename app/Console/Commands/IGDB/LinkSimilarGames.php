<?php

namespace App\Console\Commands\IGDB;

use App\Models\Game;
use App\Services\IgdbService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('import:igdb-similar-games')]
#[Description('Link similar games for existing games in DB')]
class LinkSimilarGames extends Command
{
    public function __construct(protected IgdbService $igdb)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting optimized similar games link process...');

        $stats = ['updated' => 0, 'skipped' => 0];
        $totalGames = Game::count();
        $bar = $this->output->createProgressBar($totalGames);

        Game::chunkById(50, function ($games) use (&$stats, $bar) {
            $igdbIds = $games->pluck('igdb_id')->toArray();
            $idList = implode(',', $igdbIds);

            try {
                $items = $this->igdb->query('games', "fields id, similar_games; where id = ({$idList});");

                $itemsMap = collect($items)->keyBy('id');

                foreach ($games as $game) {
                    $item = $itemsMap->get($game->igdb_id);

                    if ($item && ! empty($item['similar_games'])) {
                        $similarGameIds = Game::whereIn('igdb_id', $item['similar_games'])->pluck('id');

                        if ($similarGameIds->isNotEmpty()) {
                            $changes = $game->similarGames()->syncWithoutDetaching($similarGameIds);
                            
                            if (! empty($changes['attached'])) {
                                $stats['updated']++;
                            } else {
                                $stats['skipped']++;
                            }
                        } else {
                            $stats['skipped']++;
                        }
                    } else {
                        $stats['skipped']++;
                    }

                    $bar->advance();
                }
            } catch (\Throwable $e) {
                $this->error("\nError processing chunk: ".$e->getMessage());
            }

            usleep(250_000);
        });

        $bar->finish();
        $this->newLine();
        $this->info(sprintf('Process finished. Updated: %d | Skipped: %d', $stats['updated'], $stats['skipped']));

        return Command::SUCCESS;
    }
}

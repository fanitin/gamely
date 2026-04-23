<?php

namespace App\Console\Commands\IGDB;

use App\Exceptions\GarbageImageException;
use App\Models\Game;
use App\Services\IgdbService;
use App\Services\MediaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('import:covers {--limit= : Limit the number of games to process} {--force : Force re-download even if cover already exists in R2}')]
#[Description('Import and optimize game covers from IGDB to Cloudflare R2')]
class ImportCovers extends Command
{
    public function __construct(
        private IgdbService $igdb,
        private MediaService $media
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $forceRedownload = (bool) $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $query = Game::query()->whereNotNull('cover_igdb_id');

        if (! $forceRedownload) {
            $query->where(function ($q) {
                $q->whereNull('cover_url')
                    ->orWhere('cover_url', 'not like', 'covers/%');
            });
        }

        $total = $query->count();

        if ($limit !== null) {
            $total = min($total, $limit);
        }

        if ($total === 0) {
            $this->info('No games found needing cover import.');

            return self::SUCCESS;
        }

        $this->info("Starting cover import for {$total} games...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $success = 0;
        $failed = 0;

        $query->chunkById(50, function ($games) use (
            $bar, &$processed, &$success, &$failed, $total, $forceRedownload
        ) {
            if ($processed >= $total) {
                return false;
            }

            $chunk = $games->take($total - $processed);
            $gameIds = $chunk->pluck('igdb_id')->toArray();

            try {
                $coversData = collect($this->igdb->getCovers($gameIds))->keyBy('game');

                foreach ($chunk as $game) {
                    $cover = $coversData->get($game->igdb_id);

                    if (! $cover || empty($cover['url'])) {
                        $processed++;
                        $bar->advance();
                        continue;
                    }

                    try {
                        $path = $this->media->uploadCover($cover['url'], $game->igdb_id);
                        $game->update(['cover_url' => $path]);
                        $success++;
                    } catch (GarbageImageException $e) {
                        $this->warn("\n[Skipped] Game IGDB ID {$game->igdb_id}: Black/garbage cover skipped.");
                    } catch (Throwable $e) {
                        $this->error("\n[Error] Game IGDB ID {$game->igdb_id}: " . $e->getMessage());
                        $failed++;
                    }

                    $processed++;
                    $bar->advance();
                }

                usleep(config('services.igdb.sleep_ms', 250) * 1000);

            } catch (Throwable $e) {
                $this->error("\n[Fatal] Batch IGDB query failed: " . $e->getMessage());
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Cover import finished!");
        $this->info("Uploaded: {$success} | Failed: {$failed} | Total processed: {$processed}");

        return self::SUCCESS;
    }
}

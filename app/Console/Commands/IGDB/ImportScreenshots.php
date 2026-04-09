<?php

namespace App\Console\Commands\IGDB;

use App\Models\Game;
use App\Services\IgdbService;
use App\Services\MediaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('import:screenshots {--limit= : Limit the number of games to process} {--force : Force re-download even if screenshots already exist}')]
#[Description('Import and optimize game screenshots from IGDB to Cloudflare R2')]
class ImportScreenshots extends Command
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

        // $query = Game::query()->where('is_active', true);
        $query = Game::query();

        if (! $forceRedownload) {
            $query->has('screenshots', '<', 5);
        }

        $total = $query->count();

        if ($limit !== null) {
            $total = min($total, $limit);
        }

        if ($total === 0) {
            $this->info('No active games found needing screenshot import.');

            return self::SUCCESS;
        }

        $this->info("Starting screenshot import for {$total} games...");
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
                $screenshotsData = $this->igdb->getScreenshots($gameIds);

                $groupedScreenshots = collect($screenshotsData)->groupBy('game');

                foreach ($chunk as $game) {
                    $gameScreenshots = $groupedScreenshots->get($game->igdb_id, collect());

                    if ($gameScreenshots->isEmpty()) {
                        $processed++;
                        $bar->advance();

                        continue;
                    }

                    $screenshotsToProcess = $gameScreenshots->take(5);

                    if ($forceRedownload) {
                        $game->screenshots()->delete();
                    }

                    $currentCount = $forceRedownload ? 0 : $game->screenshots()->count();
                    $needed = 5 - $currentCount;

                    if ($needed <= 0) {
                        $processed++;
                        $bar->advance();

                        continue;
                    }

                    $screenshotsToProcess = $screenshotsToProcess->take($needed);
                    $order = $currentCount + 1;

                    foreach ($screenshotsToProcess as $screenshot) {
                        if (empty($screenshot['url']) || empty($screenshot['image_id'])) {
                            continue;
                        }

                        try {
                            $exists = $game->screenshots()->where('url', 'like', "%{$screenshot['image_id']}%")->exists();
                            if ($exists) {
                                continue;
                            }

                            $path = $this->media->uploadScreenshot($screenshot['url'], $game->igdb_id, $screenshot['image_id']);

                            $game->screenshots()->create([
                                'url' => $path,
                                'order' => $order++,
                            ]);

                            $success++;
                        } catch (Throwable $e) {
                            $this->error("\n[Error] Game IGDB ID {$game->igdb_id} Screenshot {$screenshot['image_id']}: ".$e->getMessage());
                            $failed++;
                        }
                    }

                    $processed++;
                    $bar->advance();
                }

                usleep(config('services.igdb.sleep_ms', 250) * 1000);

            } catch (Throwable $e) {
                $this->error("\n[Fatal] Batch IGDB query failed: ".$e->getMessage());
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('Screenshot import finished!');
        $this->info("Images Uploaded: {$success} | Failed: {$failed} | Games processed: {$processed}");

        return self::SUCCESS;
    }
}

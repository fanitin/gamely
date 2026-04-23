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

#[Signature('import:artworks {--limit= : Limit the number of games to process} {--force : Force re-download even if artworks already exist}')]
#[Description('Import and optimize game artworks from IGDB to Cloudflare R2')]
class ImportArtworks extends Command
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
            $query->has('artworks', '<', 3);
        }

        $total = $query->count();

        if ($limit !== null) {
            $total = min($total, $limit);
        }

        if ($total === 0) {
            $this->info('No games found needing artwork import.');

            return self::SUCCESS;
        }

        $this->info("Starting artwork import for {$total} games...");
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
                $artworksData = $this->igdb->getArtworks($gameIds);

                $groupedArtworks = collect($artworksData)->groupBy('game');

                foreach ($chunk as $game) {
                    $gameArtworks = $groupedArtworks->get($game->igdb_id, collect());

                    if ($gameArtworks->isEmpty()) {
                        $processed++;
                        $bar->advance();

                        continue;
                    }

                    $artworksToProcess = $gameArtworks->take(3);

                    if ($forceRedownload) {
                        $game->artworks()->delete();
                    }

                    $currentCount = $forceRedownload ? 0 : $game->artworks()->count();
                    $needed = 3 - $currentCount;

                    if ($needed <= 0) {
                        $processed++;
                        $bar->advance();

                        continue;
                    }

                    $order = $currentCount + 1;
                    $addedForThisGame = 0;

                    foreach ($gameArtworks as $artwork) {
                        if ($addedForThisGame >= $needed) {
                            break;
                        }

                        if (empty($artwork['url']) || empty($artwork['image_id'])) {
                            continue;
                        }

                        try {
                            $exists = $game->artworks()->where('url', 'like', "%{$artwork['image_id']}%")->exists();
                            if ($exists) {
                                continue;
                            }

                            $path = $this->media->uploadArtwork($artwork['url'], $game->igdb_id, $artwork['image_id']);

                            $game->artworks()->create([
                                'url' => $path,
                                'order' => $order++,
                            ]);

                            $success++;
                            $addedForThisGame++;
                        } catch (GarbageImageException $e) {
                            $this->warn("\n[Skipped] Game IGDB ID {$game->igdb_id}: Black/garbage artwork {$artwork['image_id']} skipped.");
                        } catch (Throwable $e) {
                            $this->error("\n[Error] Game IGDB ID {$game->igdb_id} Artwork {$artwork['image_id']}: ".$e->getMessage());
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
        $this->info('Artwork import finished!');
        $this->info("Images Uploaded: {$success} | Failed: {$failed} | Games processed: {$processed}");

        return self::SUCCESS;
    }
}

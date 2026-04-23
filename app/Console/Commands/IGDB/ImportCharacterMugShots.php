<?php

namespace App\Console\Commands\IGDB;

use App\Exceptions\GarbageImageException;
use App\Models\Character;
use App\Services\IgdbService;
use App\Services\MediaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('import:character-mug-shots {--limit= : Limit the number of characters to process}')]
#[Description('Import character mug shots from IGDB to Cloudflare R2')]
class ImportCharacterMugShots extends Command
{
    public function __construct(
        private IgdbService $igdb,
        private MediaService $media
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $query = Character::query()
            ->whereNotNull('mug_shot_igdb_id')
            ->whereNull('mug_shot_url');

        $total = $query->count();

        if ($limit !== null) {
            $total = min($total, $limit);
        }

        if ($total === 0) {
            $this->info('No characters found needing mug shot import.');

            return self::SUCCESS;
        }

        $this->info("Starting mug shot import for {$total} characters...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $success = 0;
        $failed = 0;

        $query->chunkById(50, function ($characters) use (
            $bar, &$processed, &$success, &$failed, $total
        ) {
            if ($processed >= $total) {
                return false;
            }

            $chunk = $characters->take($total - $processed);

            $mugShotIds = $chunk->pluck('mug_shot_igdb_id')->unique()->toArray();

            try {
                $idsString = implode(',', $mugShotIds);
                $igdbQuery = "fields image_id, url; where id = ({$idsString}); limit 500;";
                $mugShotsData = collect($this->igdb->query('character_mug_shots', $igdbQuery))->keyBy('id');

                foreach ($chunk as $character) {
                    $mugShotInfo = $mugShotsData->get($character->mug_shot_igdb_id);

                    if (! $mugShotInfo || empty($mugShotInfo['url'])) {
                        $processed++;
                        $bar->advance();

                        continue;
                    }

                    try {
                        if (empty($character->mug_shot_url) || ! str_contains($character->mug_shot_url, 'character_mug_shots/')) {
                            $path = $this->media->uploadCharacterMugShot($mugShotInfo['url'], $mugShotInfo['id']);

                            $character->update(['mug_shot_url' => $path]);
                        }
                        $success++;
                    } catch (GarbageImageException $e) {
                        $this->warn("\n[Skipped] Character ID {$character->id}: Black/garbage mug shot skipped.");
                    } catch (Throwable $e) {
                        $this->error("\n[Error] Character ID {$character->id}: ".$e->getMessage());
                        $failed++;
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
        $this->info('Mug shot import finished!');
        $this->info("Uploaded: {$success} | Failed: {$failed} | Total processed: {$processed}");

        return self::SUCCESS;
    }
}

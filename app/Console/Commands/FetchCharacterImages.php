<?php

namespace App\Console\Commands;

use App\Exceptions\GarbageImageException;
use App\Models\Character;
use App\Services\MediaService;
use App\Services\WikiImageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('characters:fetch-images
    {--limit= : Max number of characters to process}
    {--force : Re-download even if a local image already exists}')]
#[Description('Resolve each character\'s wiki page image via the MediaWiki API (source_url) and download it as webp into storage/app/private/character-images')]
class FetchCharacterImages extends Command
{
    private const SLEEP_MS = 300;

    public function __construct(
        private WikiImageService $wiki,
        private MediaService $media,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $disk = Storage::disk('local');

        $query = Character::query()->whereNotNull('source_url')->orderBy('id');
        $total = $limit !== null ? min($query->count(), $limit) : $query->count();

        if ($total === 0) {
            $this->info('No characters with source_url found.');

            return self::SUCCESS;
        }

        $this->info("Fetching images for up to {$total} characters...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $stats = ['downloaded' => 0, 'existing' => 0, 'no_image' => 0, 'garbage' => 0, 'failed' => 0];
        $processed = 0;

        $query->chunkById(100, function ($characters) use (&$stats, &$processed, $total, $force, $disk, $bar) {
            foreach ($characters as $character) {
                if ($processed >= $total) {
                    return false;
                }

                $processed++;
                $bar->advance();

                $localPath = MediaService::LOCAL_CHARACTER_IMAGES_DIR."/{$character->slug}.webp";

                if (! $force && $disk->exists($localPath)) {
                    $stats['existing']++;

                    continue;
                }

                try {
                    $imageUrl = $this->wiki->resolveImageUrl($character->source_url);

                    if (! $imageUrl) {
                        $stats['no_image']++;
                        $this->warn("\n[No image] {$character->name}: {$character->source_url}");

                        continue;
                    }

                    $this->media->downloadCharacterImage($imageUrl, $character->slug);
                    $character->update(['image_url' => $imageUrl]);
                    $stats['downloaded']++;
                } catch (GarbageImageException $e) {
                    $stats['garbage']++;
                    $this->warn("\n[Garbage] {$character->name}: skipped");
                } catch (Throwable $e) {
                    $stats['failed']++;
                    $this->error("\n[Error] {$character->name}: ".$e->getMessage());
                }

                usleep(self::SLEEP_MS * 1000);
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info(sprintf(
            'Downloaded: %d | Already had: %d | No image on wiki: %d | Garbage: %d | Failed: %d',
            $stats['downloaded'], $stats['existing'], $stats['no_image'], $stats['garbage'], $stats['failed']
        ));
        $this->line('Images are in '.$disk->path(MediaService::LOCAL_CHARACTER_IMAGES_DIR));

        return self::SUCCESS;
    }
}

<?php

namespace App\Services;

use App\Exceptions\GarbageImageException;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class MediaService
{
    public const LOCAL_CHARACTER_IMAGES_DIR = 'character-images';

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(Driver::class);
    }

    public function uploadCover(string $url, int $igdbId): string
    {
        return $this->processAndUploadImage($url, "covers/{$igdbId}.webp", 't_cover_big', 600);
    }

    public function uploadArtwork(string $url, int $igdbId, string $imageId): string
    {
        return $this->processAndUploadImage($url, "artworks/{$igdbId}_{$imageId}.webp", 't_1080p', 1280);
    }

    public function uploadScreenshot(string $url, int $igdbId, string $imageId): string
    {
        return $this->processAndUploadImage($url, "screenshots/{$igdbId}_{$imageId}.webp", 't_1080p', 1280);
    }

    public function uploadCharacterMugShot(string $url, int $igdbId): string
    {
        return $this->processAndUploadImage($url, "character_mug_shots/{$igdbId}.webp", 't_cover_big', 600);
    }

    public function downloadCharacterImage(string $url, string $slug): string
    {
        $path = self::LOCAL_CHARACTER_IMAGES_DIR."/{$slug}.webp";

        try {
            Storage::disk('local')->put($path, $this->fetchAsWebp($url, 600));

            return $path;
        } catch (GarbageImageException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("MediaService::downloadCharacterImage failed for {$path}: ".$e->getMessage(), 0, $e);
        }
    }

    private function processAndUploadImage(string $url, string $path, string $igdbSize, int $maxWidth): string
    {
        $sourceUrl = str_replace('t_thumb', $igdbSize, $url);
        if (! str_starts_with($sourceUrl, 'http')) {
            $sourceUrl = 'https:'.$sourceUrl;
        }

        try {
            Storage::disk('r2')->put($path, $this->fetchAsWebp($sourceUrl, $maxWidth), [
                'ContentType' => 'image/webp',
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);

            return $path;
        } catch (GarbageImageException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("MediaService::processAndUploadImage failed for {$path}: ".$e->getMessage(), 0, $e);
        }
    }

    private function fetchAsWebp(string $sourceUrl, int $maxWidth): string
    {
        $response = Http::withHeaders(['User-Agent' => WikiImageService::USER_AGENT])->timeout(30)->get($sourceUrl);

        if (! $response->successful()) {
            throw new Exception("Failed to download image from {$sourceUrl}. Status: {$response->status()}");
        }

        $image = $this->manager->decode($response->body());

        if ($this->isGarbageImage($image)) {
            throw new GarbageImageException("Image from {$sourceUrl} is detected as garbage (too dark/black).");
        }

        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        return $image->encode(new WebpEncoder(quality: 82))->toString();
    }

    public function deleteCover(?string $path): void
    {
        $this->deleteFromR2($path, 'covers/', __FUNCTION__);
    }

    public function deleteCharacterMugShot(?string $path): void
    {
        $this->deleteFromR2($path, 'character_mug_shots/', __FUNCTION__);
    }

    private function deleteFromR2(?string $path, string $prefix, string $caller): void
    {
        if (! config('services.igdb.media_import')) {
            return;
        }

        if (! is_string($path) || $path === '' || ! str_starts_with($path, $prefix)) {
            return;
        }

        try {
            Storage::disk('r2')->delete($path);
        } catch (Exception $e) {
            throw new Exception("MediaService::{$caller} failed: ".$e->getMessage(), 0, $e);
        }
    }

    private function isGarbageImage(ImageInterface $image): bool
    {
        $sample = (clone $image)->resize(4, 4);

        $firstColor = $sample->colorAt(0, 0);
        $channels = $firstColor->channels();
        $firstBrightness = ($channels[0]->value() * 0.299) + ($channels[1]->value() * 0.587) + ($channels[2]->value() * 0.114);

        if ($firstBrightness > 15) {
            return false;
        }

        for ($x = 0; $x < 4; $x++) {
            for ($y = 0; $y < 4; $y++) {
                $color = $sample->colorAt($x, $y);
                $channels = $color->channels();
                $brightness = ($channels[0]->value() * 0.299) + ($channels[1]->value() * 0.587) + ($channels[2]->value() * 0.114);

                if (abs($brightness - $firstBrightness) > 5) {
                    return false;
                }
            }
        }

        return true;
    }
}

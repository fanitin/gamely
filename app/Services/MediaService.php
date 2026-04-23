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

    private function processAndUploadImage(string $url, string $path, string $igdbSize, int $maxWidth): string
    {
        $sourceUrl = str_replace('t_thumb', $igdbSize, $url);
        if (! str_starts_with($sourceUrl, 'http')) {
            $sourceUrl = 'https:'.$sourceUrl;
        }

        try {
            $response = Http::timeout(30)->get($sourceUrl);

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

            $encoded = $image->encode(new WebpEncoder(quality: 82));

            Storage::disk('r2')->put($path, $encoded->toString(), [
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

    public function deleteCover(?string $path): void
    {
        if (! is_string($path) || $path === '' || ! str_starts_with($path, 'covers/')) {
            return;
        }

        try {
            Storage::disk('r2')->delete($path);
        } catch (Exception $e) {
            throw new Exception('MediaService::deleteCover failed: '.$e->getMessage(), 0, $e);
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

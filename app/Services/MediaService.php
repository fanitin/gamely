<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class MediaService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(Driver::class);
    }

    public function uploadCover(string $url, int $igdbId): string
    {
        $sourceUrl = str_replace('t_thumb', 't_cover_big', $url);
        if (! str_starts_with($sourceUrl, 'http')) {
            $sourceUrl = 'https:' . $sourceUrl;
        }

        try {
            $response = Http::timeout(30)->get($sourceUrl);

            if (! $response->successful()) {
                throw new Exception("Failed to download image from {$sourceUrl}. Status: {$response->status()}");
            }

            $image = $this->manager->decode($response->body());

            if ($image->width() > 600) {
                $image->scale(width: 600);
            }

            $encoded = $image->encode(new WebpEncoder(quality: 82));

            $path = "covers/{$igdbId}.webp";

            Storage::disk('r2')->put($path, $encoded->toString(), [
                'ContentType' => 'image/webp',
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);

            return $path;
        } catch (Exception $e) {
            throw new Exception('MediaService::uploadCover failed: ' . $e->getMessage(), 0, $e);
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
            throw new Exception('MediaService::deleteCover failed: ' . $e->getMessage(), 0, $e);
        }
    }
}

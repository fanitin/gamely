<?php

namespace Tests\Unit;

use App\Exceptions\GarbageImageException;
use App\Services\MediaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    public function test_it_detects_black_image_as_garbage()
    {
        Storage::fake('r2');

        $img = \imagecreatetruecolor(10, 10);
        \ob_start();
        \imagepng($img);
        $blackImageContent = \ob_get_clean();
        \imagedestroy($img);

        Http::fake([
            'https://images.igdb.com/igdb/image/upload/t_cover_big/test.jpg' => Http::response($blackImageContent, 200),
        ]);

        $service = new MediaService;

        $this->expectException(GarbageImageException::class);
        $this->expectExceptionMessage('detected as garbage');

        $service->uploadCover('//images.igdb.com/igdb/image/upload/t_thumb/test.jpg', 123);
    }

    public function test_it_accepts_normal_image()
    {
        Storage::fake('r2');

        $img = \imagecreatetruecolor(10, 10);
        $white = \imagecolorallocate($img, 255, 255, 255);
        \imagefill($img, 0, 0, $white);
        \ob_start();
        \imagepng($img);
        $whiteImageContent = \ob_get_clean();
        \imagedestroy($img);

        Http::fake([
            'https://images.igdb.com/igdb/image/upload/t_cover_big/test.jpg' => Http::response($whiteImageContent, 200),
        ]);

        $service = new MediaService;
        $path = $service->uploadCover('//images.igdb.com/igdb/image/upload/t_thumb/test.jpg', 123);

        $this->assertEquals('covers/123.webp', $path);
        Storage::disk('r2')->assertExists('covers/123.webp');
    }

    public function test_it_deletes_media_from_r2_when_media_import_is_enabled()
    {
        Storage::fake('r2');
        Storage::disk('r2')->put('covers/1.webp', 'x');
        Storage::disk('r2')->put('character_mug_shots/2.webp', 'x');

        $service = new MediaService;
        $service->deleteCover('covers/1.webp');
        $service->deleteCharacterMugShot('character_mug_shots/2.webp');
        $service->deleteCharacterMugShot('covers/1.webp');

        Storage::disk('r2')->assertMissing('covers/1.webp');
        Storage::disk('r2')->assertMissing('character_mug_shots/2.webp');
    }

    public function test_it_never_touches_r2_when_media_import_is_disabled()
    {
        config(['services.igdb.media_import' => false]);
        Storage::fake('r2');
        Storage::disk('r2')->put('covers/1.webp', 'x');
        Storage::disk('r2')->put('character_mug_shots/2.webp', 'x');

        $service = new MediaService;
        $service->deleteCover('covers/1.webp');
        $service->deleteCharacterMugShot('character_mug_shots/2.webp');

        Storage::disk('r2')->assertExists('covers/1.webp');
        Storage::disk('r2')->assertExists('character_mug_shots/2.webp');
    }
}

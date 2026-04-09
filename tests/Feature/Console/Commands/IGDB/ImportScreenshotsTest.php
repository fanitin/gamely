<?php

namespace Tests\Feature\Console\Commands\IGDB;

use App\Models\Game;
use App\Models\GameScreenshot;
use App\Services\IgdbService;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportScreenshotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_screenshots_command_downloads_and_saves_screenshots(): void
    {
        $game = Game::create([
            'name' => 'BioShock',
            'slug' => 'bioshock',
            'igdb_id' => 20,
            'is_active' => true,
        ]);

        $this->instance(
            IgdbService::class,
            Mockery::mock(IgdbService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getScreenshots')
                    ->once()
                    ->with([20])
                    ->andReturn([
                        [
                            'game' => 20,
                            'image_id' => 'scr1',
                            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/scr1.jpg'
                        ],
                        [
                            'game' => 20,
                            'image_id' => 'scr2',
                            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/scr2.jpg'
                        ]
                    ]);
            })
        );

        $this->instance(
            MediaService::class,
            Mockery::mock(MediaService::class, function (MockInterface $mock) {
                $mock->shouldReceive('uploadScreenshot')
                    ->twice()
                    ->andReturn('screenshots/20_scr1.webp', 'screenshots/20_scr2.webp');
            })
        );

        $this->artisan('import:screenshots')
            ->expectsOutputToContain('Starting screenshot import for 1 games...')
            ->expectsOutputToContain('Screenshot import finished!')
            ->assertExitCode(0);

        $this->assertCount(2, $game->fresh()->screenshots);
        $this->assertEquals('screenshots/20_scr1.webp', $game->screenshots->first()->url);
        $this->assertEquals(1, $game->screenshots->first()->order);
    }

    public function test_import_screenshots_limits_to_five_images(): void
    {
        $game = Game::create([
            'name' => 'BioShock',
            'slug' => 'bioshock',
            'igdb_id' => 20,
            'is_active' => true,
        ]);

        $this->instance(
            IgdbService::class,
            Mockery::mock(IgdbService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getScreenshots')
                    ->andReturn([
                        ['game' => 20, 'image_id' => 's1', 'url' => 'u1'],
                        ['game' => 20, 'image_id' => 's2', 'url' => 'u2'],
                        ['game' => 20, 'image_id' => 's3', 'url' => 'u3'],
                        ['game' => 20, 'image_id' => 's4', 'url' => 'u4'],
                        ['game' => 20, 'image_id' => 's5', 'url' => 'u5'],
                        ['game' => 20, 'image_id' => 's6', 'url' => 'u6'],
                    ]);
            })
        );

        $this->instance(
            MediaService::class,
            Mockery::mock(MediaService::class, function (MockInterface $mock) {
                $mock->shouldReceive('uploadScreenshot')->times(5)->andReturn('path');
            })
        );

        $this->artisan('import:screenshots')->assertExitCode(0);

        $this->assertCount(5, $game->fresh()->screenshots);
    }
}

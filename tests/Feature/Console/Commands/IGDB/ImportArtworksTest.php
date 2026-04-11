<?php

namespace Tests\Feature\Console\Commands\IGDB;

use App\Models\Game;
use App\Models\GameArtwork;
use App\Services\IgdbService;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportArtworksTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_artworks_command_downloads_and_saves_artworks(): void
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
                $mock->shouldReceive('getArtworks')
                    ->once()
                    ->with([20])
                    ->andReturn([
                        [
                            'game' => 20,
                            'image_id' => 'art1',
                            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/art1.jpg'
                        ],
                        [
                            'game' => 20,
                            'image_id' => 'art2',
                            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/art2.jpg'
                        ],
                        [
                            'game' => 20,
                            'image_id' => 'art3',
                            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/art3.jpg'
                        ]
                    ]);
            })
        );

        $this->instance(
            MediaService::class,
            Mockery::mock(MediaService::class, function (MockInterface $mock) {
                $mock->shouldReceive('uploadArtwork')
                    ->times(3)
                    ->andReturn('artworks/20_art1.webp', 'artworks/20_art2.webp', 'artworks/20_art3.webp');
            })
        );

        $this->artisan('import:artworks')
            ->expectsOutputToContain('Starting artwork import for 1 games...')
            ->expectsOutputToContain('Artwork import finished!')
            ->assertExitCode(0);

        $this->assertCount(3, $game->fresh()->artworks);
        $this->assertEquals('artworks/20_art1.webp', $game->artworks->first()->url);
        $this->assertEquals(1, $game->artworks->first()->order);
    }

    public function test_import_artworks_limits_to_three_images(): void
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
                $mock->shouldReceive('getArtworks')
                    ->andReturn([
                        ['game' => 20, 'image_id' => 'art1', 'url' => 'u1'],
                        ['game' => 20, 'image_id' => 'art2', 'url' => 'u2'],
                        ['game' => 20, 'image_id' => 'art3', 'url' => 'u3'],
                        ['game' => 20, 'image_id' => 'art4', 'url' => 'u4'],
                    ]);
            })
        );

        $this->instance(
            MediaService::class,
            Mockery::mock(MediaService::class, function (MockInterface $mock) {
                $mock->shouldReceive('uploadArtwork')->times(3)->andReturn('path');
            })
        );

        $this->artisan('import:artworks')->assertExitCode(0);

        $this->assertCount(3, $game->fresh()->artworks);
    }
}

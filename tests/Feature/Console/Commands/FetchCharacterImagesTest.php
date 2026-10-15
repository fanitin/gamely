<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Character;
use App\Services\MediaService;
use App\Services\WikiImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class FetchCharacterImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_downloads_images_for_characters_with_a_wiki_page_and_skips_existing(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put(MediaService::LOCAL_CHARACTER_IMAGES_DIR.'/mario.webp', 'x');

        Character::create(['name' => 'Geralt', 'slug' => 'geralt', 'source_url' => 'https://witcher.fandom.com/wiki/Geralt_of_Rivia']);
        Character::create(['name' => 'Mario', 'slug' => 'mario', 'source_url' => 'https://mario.fandom.com/wiki/Mario']);
        Character::create(['name' => 'Nobody', 'slug' => 'nobody', 'source_url' => 'https://zelda.fandom.com/wiki/Nobody']);
        Character::create(['name' => 'No source', 'slug' => 'no-source']);

        $this->instance(WikiImageService::class, Mockery::mock(WikiImageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('resolveImageUrl')->once()->with('https://witcher.fandom.com/wiki/Geralt_of_Rivia')->andReturn('https://static.wikia.nocookie.net/geralt.png');
            $mock->shouldReceive('resolveImageUrl')->once()->with('https://zelda.fandom.com/wiki/Nobody')->andReturn(null);
        }));

        $this->instance(MediaService::class, Mockery::mock(MediaService::class, function (MockInterface $mock) {
            $mock->shouldReceive('downloadCharacterImage')->once()->with('https://static.wikia.nocookie.net/geralt.png', 'geralt')->andReturn('character-images/geralt.webp');
        }));

        $this->artisan('characters:fetch-images')
            ->expectsOutputToContain('Downloaded: 1 | Already had: 1 | No image on wiki: 1 | Garbage: 0 | Failed: 0')
            ->assertExitCode(0);

        $this->assertSame('https://static.wikia.nocookie.net/geralt.png', Character::where('slug', 'geralt')->value('image_url'));
        $this->assertNull(Character::where('slug', 'nobody')->value('image_url'));
    }
}

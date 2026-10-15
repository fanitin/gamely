<?php

namespace Tests\Unit;

use App\Services\WikiImageService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WikiImageServiceTest extends TestCase
{
    public function test_resolves_fandom_page_image_through_api(): void
    {
        Http::fake([
            'witcher.fandom.com/api.php*' => Http::response([
                'query' => ['pages' => ['123' => ['title' => 'Geralt of Rivia', 'original' => ['source' => 'https://static.wikia.nocookie.net/witcher/images/geralt.png']]]],
            ]),
        ]);

        $url = (new WikiImageService)->resolveImageUrl('https://witcher.fandom.com/wiki/Geralt_of_Rivia');

        $this->assertSame('https://static.wikia.nocookie.net/witcher/images/geralt.png', $url);

        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://witcher.fandom.com/api.php?')
                && $request['titles'] === 'Geralt_of_Rivia'
                && $request['prop'] === 'pageimages'
                && $request->hasHeader('User-Agent');
        });
    }

    public function test_uses_wikipedia_api_path_and_decodes_title(): void
    {
        Http::fake([
            'en.wikipedia.org/w/api.php*' => Http::response([
                'query' => ['pages' => ['1' => ['original' => ['source' => 'https://upload.wikimedia.org/x.jpg']]]],
            ]),
        ]);

        $url = (new WikiImageService)->resolveImageUrl('https://en.wikipedia.org/wiki/Lara_Croft_%28character%29');

        $this->assertSame('https://upload.wikimedia.org/x.jpg', $url);
        Http::assertSent(fn ($request) => $request['titles'] === 'Lara_Croft_(character)');
    }

    public function test_returns_null_when_page_has_no_image_or_url_is_not_a_wiki_page(): void
    {
        Http::fake([
            '*' => Http::response(['query' => ['pages' => ['-1' => ['missing' => '']]]]),
        ]);

        $service = new WikiImageService;

        $this->assertNull($service->resolveImageUrl('https://zelda.fandom.com/wiki/Nobody'));
        $this->assertNull($service->resolveImageUrl('https://example.com/not-a-wiki'));
        $this->assertNull($service->resolveImageUrl(''));
    }
}

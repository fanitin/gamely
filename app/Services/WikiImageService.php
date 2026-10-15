<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WikiImageService
{
    public const USER_AGENT = 'GamelyBot/1.0 (+https://gamely.app; character image fetcher)';

    public function resolveImageUrl(string $pageUrl): ?string
    {
        $parts = parse_url(trim($pageUrl));

        if (empty($parts['host']) || empty($parts['path']) || ! preg_match('#/wiki/(.+)$#', $parts['path'], $matches)) {
            return null;
        }

        $title = urldecode($matches[1]);
        $apiUrl = $this->apiUrl($parts['scheme'] ?? 'https', $parts['host']);

        $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
            ->timeout(20)
            ->get($apiUrl, [
                'action' => 'query',
                'prop' => 'pageimages',
                'piprop' => 'original',
                'redirects' => 1,
                'format' => 'json',
                'titles' => $title,
            ]);

        if (! $response->successful()) {
            return null;
        }

        foreach ($response->json('query.pages', []) as $page) {
            if (! empty($page['original']['source'])) {
                return $page['original']['source'];
            }
        }

        return null;
    }

    private function apiUrl(string $scheme, string $host): string
    {
        $path = str_ends_with($host, 'wikipedia.org') ? '/w/api.php' : '/api.php';

        return "{$scheme}://{$host}{$path}";
    }
}

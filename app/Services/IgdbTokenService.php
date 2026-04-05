<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IgdbTokenService
{
    public function getToken(): string
    {
        return Cache::remember('igdb_access_token', now()->addDays(50), function () {
            return $this->fetchNewToken();
        });
    }

    public function fetchNewToken(): string
    {
        $response = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => config('services.igdb.client_id'),
            'client_secret' => config('services.igdb.client_secret'),
            'grant_type' => 'client_credentials',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch IGDB token: '.$response->body());
        }

        return $response->json('access_token');
    }

    public function forgetToken(): void
    {
        Cache::forget('igdb_access_token');
    }
}

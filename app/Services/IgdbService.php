<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class IgdbService
{
    const ALLOWED_PLATFORM_IDS = [
        // Desktop
        6,   // PC
        14,  // Mac
        3,   // Linux

        // PlayStation
        7,   // PS1
        8,   // PS2
        9,   // PS3
        48,  // PS4
        167, // PS5
        38,  // PSP
        46,  // Vita
        165, // PSVR
        390, // PSVR2

        // Xbox
        11,  // Xbox (original)
        12,  // Xbox 360
        49,  // Xbox One
        169, // Xbox Series X|S

        // Nintendo
        18,  // NES
        19,  // SNES
        4,   // N64
        21,  // GameCube
        5,   // Wii
        41,  // Wii U
        130, // Switch
        508, // Switch 2
        33,  // Game Boy
        22,  // GBC
        24,  // GBA
        20,  // NDS
        37,  // 3DS
        137, // New 3DS

        // Sega (ретро, но знаковые)
        29,  // Mega Drive / Genesis
        32,  // Saturn
        23,  // Dreamcast

        // Mobile
        34,  // Android
        39,  // iOS

        // VR (современные)
        162, // Oculus / Meta Quest
        163, // Steam VR
        386, // Meta Quest 2
        471, // Meta Quest 3

        52,  // Arcade
    ];

    public function __construct(
        private IgdbTokenService $tokenService,
    ) {}

    public function query(string $endpoint, string $body): array
    {
        $response = $this->makeRequest($endpoint, $body);

        if ($response->status() === 401) {
            $this->tokenService->forgetToken();
            $response = $this->makeRequest($endpoint, $body);
        }

        $response->throw();

        return $response->json();
    }

    public function getCovers(array $gameIds): array
    {
        if (empty($gameIds)) {
            return [];
        }

        $idsString = implode(',', $gameIds);
        $query = "fields game, image_id, url; where game = ({$idsString}); limit 500;";

        return $this->query('covers', $query);
    }

    private function makeRequest(string $endpoint, string $body): Response
    {
        return Http::withHeaders([
            'Client-ID' => config('services.igdb.client_id'),
            'Authorization' => 'Bearer '.$this->tokenService->getToken(),
            'Accept' => 'application/json',
        ])->withBody($body, 'text/plain')
            ->post(config('services.igdb.base_url').'/'.$endpoint);
    }
}

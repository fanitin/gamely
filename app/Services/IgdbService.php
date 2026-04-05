<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class IgdbService
{
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

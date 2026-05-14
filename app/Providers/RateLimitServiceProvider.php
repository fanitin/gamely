<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->defineLimiter('game-search', 240, 'Too many search requests. Please retry later.');
        $this->defineLimiter('game-guess', 60, 'Too many guess requests. Please retry later.');
        $this->defineLimiter('game-challenge', 60, 'Too many challenge requests. Please retry later.');
        $this->defineLimiter('stats-read', 120, 'Too many stats requests. Please retry later.');
    }

    private function defineLimiter(string $name, int $perMinute, string $message): void
    {
        RateLimiter::for($name, function (Request $request) use ($perMinute, $message) {
            return Limit::perMinute($perMinute)
                ->by($this->visitorKey($request))
                ->response(function (Request $request, array $headers) use ($message) {
                    return response()->json([
                        'code' => 'rate_limited',
                        'message' => $message,
                        'error' => $message,
                        'retry_after' => (int) ($headers['Retry-After'] ?? 60),
                    ], 429, $headers);
                });
        });
    }

    private function visitorKey(Request $request): string
    {
        $sessionToken = (string) $request->cookie('session_token', '');

        return $request->ip().'|'.($sessionToken !== '' ? $sessionToken : sha1((string) $request->userAgent()));
    }
}

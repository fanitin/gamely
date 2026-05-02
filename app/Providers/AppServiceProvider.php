<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\GameSession;
use App\Observers\GameObserver;
use App\Observers\GameSessionObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Game::observe(GameObserver::class);
        GameSession::observe(GameSessionObserver::class);

        RateLimiter::for('game-search', function (Request $request) {
            $sessionToken = (string) $request->cookie('session_token', '');
            $visitorKey = $request->ip().'|'.($sessionToken !== '' ? $sessionToken : sha1((string) $request->userAgent()));

            return Limit::perMinute(240)
                ->by($visitorKey)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many search requests. Please retry later.',
                        'retry_after' => (int) ($headers['Retry-After'] ?? 60),
                    ], 429, $headers);
                });
        });

        RateLimiter::for('game-guess', function (Request $request) {
            $sessionToken = (string) $request->cookie('session_token', '');
            $visitorKey = $request->ip().'|'.($sessionToken !== '' ? $sessionToken : sha1((string) $request->userAgent()));

            return Limit::perMinute(60)
                ->by($visitorKey)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many guess requests. Please retry later.',
                        'retry_after' => (int) ($headers['Retry-After'] ?? 60),
                    ], 429, $headers);
                });
        });

        RateLimiter::for('game-challenge', function (Request $request) {
            $sessionToken = (string) $request->cookie('session_token', '');
            $visitorKey = $request->ip().'|'.($sessionToken !== '' ? $sessionToken : sha1((string) $request->userAgent()));

            return Limit::perMinute(60)
                ->by($visitorKey)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many challenge requests. Please retry later.',
                        'retry_after' => (int) ($headers['Retry-After'] ?? 60),
                    ], 429, $headers);
                });
        });
    }
}

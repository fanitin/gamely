<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\GameSession;
use App\Observers\GameObserver;
use App\Observers\GameSessionObserver;
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
    }
}

<?php

namespace App\Observers;

use App\Models\Game;
use App\Services\MediaService;

class GameObserver
{
    public function updated(Game $game): void
    {
        if (! $game->wasChanged('cover_url')) {
            return;
        }

        $originalCover = $game->getOriginal('cover_url');
        $currentCover = $game->cover_url;

        if ($originalCover === $currentCover) {
            return;
        }

        app(MediaService::class)->deleteCover($originalCover);
    }

    public function deleted(Game $game): void
    {
        app(MediaService::class)->deleteCover($game->cover_url);
    }
}

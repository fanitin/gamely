<?php

namespace App\Observers;

use App\Models\GameSession;
use App\Models\SiteStats;

class GameSessionObserver
{
    public function created(GameSession $session): void
    {
        if ($session->challenge_id) {
            SiteStats::incrementStats($session);
        }
    }
}

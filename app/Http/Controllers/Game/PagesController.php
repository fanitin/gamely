<?php

namespace App\Http\Controllers\Game;

use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Models\DailyChallenge;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home', []);
    }

    public function classic(): Response
    {
        return Inertia::render('Classic', [
            'solvedToday' => $this->solvedTodayForMode(GameMode::CLASSIC),
        ]);
    }

    public function screenshots(): Response
    {
        return Inertia::render('Screenshots', [
            'solvedToday' => $this->solvedTodayForMode(GameMode::GAME_SCREENSHOTS),
        ]);
    }

    public function character(): Response
    {
        return Inertia::render('Character', [
            'solvedToday' => $this->solvedTodayForMode(GameMode::CHARACTER),
        ]);
    }

    private function solvedTodayForMode(GameMode $mode): int
    {
        $challenge = DailyChallenge::query()
            ->forMode($mode)
            ->forDate(today()->toDateString())
            ->with('stats')
            ->first();

        if (! $challenge || ! $challenge->stats) {
            return 0;
        }

        return (int) $challenge->stats->total_players;
    }
}

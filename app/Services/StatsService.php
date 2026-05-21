<?php

namespace App\Services;

use App\Enums\GameMode;
use App\Models\DailyChallenge;
use App\Models\GameSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class StatsService
{
    public function getAvgAttemptsPerMode(): array
    {
        return Cache::remember('avg_attempts_per_mode', 1800, function () {
            $from = Carbon::now()->subDays(30)->toDateString();
            $to   = Carbon::now()->toDateString();

            $rows = GameSession::query()
                ->join('daily_challenges', 'daily_challenges.id', '=', 'game_sessions.challenge_id')
                ->whereBetween('daily_challenges.date', [$from, $to])
                ->groupBy('game_sessions.mode')
                ->selectRaw('game_sessions.mode as mode, ROUND(AVG(game_sessions.attempts), 1) as avg')
                ->pluck('avg', 'mode');

            return [
                GameMode::CLASSIC->value          => isset($rows[GameMode::CLASSIC->value]) ? (float) $rows[GameMode::CLASSIC->value] : null,
                GameMode::GAME_SCREENSHOTS->value  => isset($rows[GameMode::GAME_SCREENSHOTS->value]) ? (float) $rows[GameMode::GAME_SCREENSHOTS->value] : null,
                GameMode::CHARACTER->value         => isset($rows[GameMode::CHARACTER->value]) ? (float) $rows[GameMode::CHARACTER->value] : null,
            ];
        });
    }

    public function getModeDistribution(GameMode $mode, string $date): array
    {
        $challenge = DailyChallenge::query()
            ->with('stats')
            ->forMode($mode)
            ->forDate($date)
            ->first();

        if (! $challenge || ! $challenge->stats) {
            return [
                'mode' => $mode->value,
                'date' => $date,
                'total_players' => 0,
                'bins' => [],
            ];
        }

        $distribution = $challenge->stats->attempts_distribution ?? [];
        $bins = [];
        foreach ($distribution as $attempts => $players) {
            $bins[] = [
                'attempts' => (int) $attempts,
                'players' => (int) $players,
            ];
        }

        usort($bins, fn ($a, $b) => $a['attempts'] <=> $b['attempts']);

        return [
            'mode' => $mode->value,
            'date' => $date,
            'total_players' => $challenge->stats->total_players ?? 0,
            'bins' => $bins,
        ];
    }

    public function getSolvedTodaySnapshot(string $date): array
    {
        $counts = DailyChallenge::query()
            ->whereDate('date', $date)
            ->leftJoin('site_stats', 'site_stats.challenge_id', '=', 'daily_challenges.id')
            ->groupBy('daily_challenges.mode')
            ->selectRaw('daily_challenges.mode as mode, COALESCE(SUM(site_stats.total_players), 0) as players')
            ->pluck('players', 'mode');

        $classic = (int) ($counts[GameMode::CLASSIC->value] ?? 0);
        $screenshots = (int) ($counts[GameMode::GAME_SCREENSHOTS->value] ?? 0);
        $character = (int) ($counts[GameMode::CHARACTER->value] ?? 0);

        return [
            'date' => $date,
            'classic_players' => $classic,
            'screenshots_players' => $screenshots,
            'character_players' => $character,
            'total_players' => $classic + $screenshots + $character,
        ];
    }

    public function getDailyTrend(string $from, string $to): array
    {
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->startOfDay();

        $raw = GameSession::query()
            ->join('daily_challenges', 'daily_challenges.id', '=', 'game_sessions.challenge_id')
            ->whereBetween('daily_challenges.date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->groupBy('daily_challenges.date', 'game_sessions.mode')
            ->selectRaw('DATE(daily_challenges.date) as date, game_sessions.mode as mode, AVG(game_sessions.attempts) as avg_attempts')
            ->orderBy('date')
            ->get();

        $indexed = [];
        foreach ($raw as $row) {
            $indexed[$row->date][$row->mode->value] = round((float) $row->avg_attempts, 2);
        }

        $points = [];
        $cursor = $fromDate->copy();
        while ($cursor->lte($toDate)) {
            $date = $cursor->toDateString();
            $classic = $indexed[$date][GameMode::CLASSIC->value] ?? null;
            $screenshots = $indexed[$date][GameMode::GAME_SCREENSHOTS->value] ?? null;
            $character = $indexed[$date][GameMode::CHARACTER->value] ?? null;

            $values = array_values(array_filter([$classic, $screenshots, $character], fn ($v) => $v !== null));
            $aggregate = count($values) > 0 ? round(array_sum($values) / count($values), 2) : null;

            $points[] = [
                'date' => $date,
                'classic_avg_attempts' => $classic,
                'screenshots_avg_attempts' => $screenshots,
                'character_avg_attempts' => $character,
                'aggregate_avg_attempts' => $aggregate,
            ];

            $cursor->addDay();
        }

        return [
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'points' => $points,
        ];
    }
}

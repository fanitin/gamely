<?php

namespace App\Http\Controllers;

use App\Enums\GameMode;
use App\Http\Requests\Stats\DailyTrendRequest;
use App\Http\Requests\Stats\ModeDistributionRequest;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __construct(private StatsService $statsService) {}

    public function modeDistribution(ModeDistributionRequest $request, string $mode): JsonResponse
    {
        $parsedMode = GameMode::tryFrom($mode);
        if (! $parsedMode) {
            return response()->json(['error' => 'Invalid mode'], 422);
        }

        $date = $request->validated('date', today()->toDateString());

        return response()->json($this->statsService->getModeDistribution($parsedMode, $date));
    }

    public function solvedToday(): JsonResponse
    {
        return response()->json($this->statsService->getSolvedTodaySnapshot(today()->toDateString()));
    }

    public function dailyTrend(DailyTrendRequest $request): JsonResponse
    {
        $from = $request->validated('from', today()->subDays(13)->toDateString());
        $to = $request->validated('to', today()->toDateString());

        return response()->json($this->statsService->getDailyTrend($from, $to));
    }
}

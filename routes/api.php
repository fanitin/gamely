<?php

use App\Http\Controllers\Game\GameApiController;
use App\Http\Controllers\Game\ChallengeController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->name('api.')->group(function () {
    Route::get('/game-search', [GameApiController::class, 'search'])
        ->middleware('throttle:game-search')
        ->name('game-search');
    Route::post('/guess', [GameApiController::class, 'guess'])
        ->middleware('throttle:game-guess')
        ->name('guess');

    Route::prefix('challenge')
        ->middleware('throttle:game-challenge')
        ->name('challenge.')
        ->group(function () {
        Route::get('/classic', [ChallengeController::class, 'classic'])->name('classic');
        Route::get('/screenshots', [ChallengeController::class, 'screenshots'])->name('screenshots');
        Route::get('/character', [ChallengeController::class, 'character'])->name('character');
    });

    Route::prefix('stats')
        ->middleware('throttle:stats-read')
        ->name('stats.')
        ->group(function () {
            Route::get('/modes/{mode}/distribution', [StatsController::class, 'modeDistribution'])->name('mode-distribution');
            Route::get('/solved-today', [StatsController::class, 'solvedToday'])->name('solved-today');
            Route::get('/daily-trend', [StatsController::class, 'dailyTrend'])->name('daily-trend');
        });
});

<?php

use App\Http\Controllers\Game\GameApiController;
use App\Http\Controllers\Game\ChallengeController;
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
});

<?php

use App\Http\Controllers\Game\GameApiController;
use App\Http\Controllers\Game\ChallengeController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->name('api.')->group(function () {
    Route::get('/game-search', [GameApiController::class, 'search'])->name('game-search');
    Route::post('/guess', [GameApiController::class, 'guess'])->name('guess');

    Route::prefix('challenge')->name('challenge.')->group(function () {
        Route::get('/classic', [ChallengeController::class, 'classic'])->name('classic');
        Route::get('/screenshots', [ChallengeController::class, 'screenshots'])->name('screenshots');
        Route::get('/character-attributes', [ChallengeController::class, 'characterAttributes'])->name('character-attributes');
        Route::get('/character-image', [ChallengeController::class, 'characterImage'])->name('character-image');
    });
});

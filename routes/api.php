<?php

use App\Http\Controllers\Game\GameApiController;
use App\Http\Controllers\Game\ScreenshotsChallengeController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->name('api.')->group(function () {
    Route::get('/game-search', [GameApiController::class, 'search'])->name('game-search');
    Route::post('/guess', [GameApiController::class, 'guess'])->name('guess');

    Route::get('/challenge/screenshots', [ScreenshotsChallengeController::class, 'today'])->name('challenge.screenshots');
});

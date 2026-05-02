<?php

use App\Http\Controllers\Game\PagesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PagesController::class, 'home'])->name('home');
Route::get('/classic', [PagesController::class, 'classic'])->name('classic');
Route::get('/screenshots', [PagesController::class, 'screenshots'])->name('screenshots');
Route::get('/character', [PagesController::class, 'character'])->name('character');

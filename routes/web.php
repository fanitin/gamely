<?php

use App\Http\Controllers\Game\PagesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PagesController::class, 'home'])->name('home');
Route::get('/classic', [PagesController::class, 'classic'])->name('classic');
Route::get('/screenshots', [PagesController::class, 'screenshots'])->name('screenshots');
Route::get('/character', [PagesController::class, 'character'])->name('character');

Route::get('/privacy-policy', [PagesController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms', [PagesController::class, 'terms'])->name('legal.terms');
Route::get('/cookie-policy', [PagesController::class, 'cookie'])->name('legal.cookie');

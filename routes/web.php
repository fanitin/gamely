<?php

use App\Http\Controllers\Game\PagesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PagesController::class, 'home'])->name('home');
Route::get('/attributes', [PagesController::class, 'classic'])->name('classic');

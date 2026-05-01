<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
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
        return Inertia::render('Classic', []);
    }

    public function screenshots(): Response
    {
        return Inertia::render('Screenshots', []);
    }

    public function characterAttributes(): Response
    {
        return Inertia::render('CharacterAttributes', []);
    }

    public function characterSilhouette(): Response
    {
        return Inertia::render('CharacterSilhouette', []);
    }
}

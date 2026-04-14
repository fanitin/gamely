<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home');
    }

    public function classic(): Response
    {
        return Inertia::render('Classic');
    }
}

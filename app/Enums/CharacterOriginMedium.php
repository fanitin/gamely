<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterOriginMedium: string
{
    use HasValues;

    case VIDEO_GAME = 'video_game';
    case COMIC = 'comic';
    case ANIME = 'anime';
    case MANGA = 'manga';
    case MOVIE = 'movie';
    case TV = 'tv';
    case BOOK = 'book';
    case TOY = 'toy';
    case MYTHOLOGY = 'mythology';
    case OTHER = 'other';
}

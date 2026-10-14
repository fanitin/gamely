<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterAlignment: string
{
    use HasValues;

    case HERO = 'hero';
    case ANTI_HERO = 'anti_hero';
    case NEUTRAL = 'neutral';
    case VILLAIN = 'villain';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterStatus: string
{
    use HasValues;

    case ALIVE = 'alive';
    case DEAD = 'dead';
    case UNDEAD = 'undead';
    case UNKNOWN = 'unknown';
}

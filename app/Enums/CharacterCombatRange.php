<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterCombatRange: string
{
    use HasValues;

    case MELEE = 'melee';
    case RANGED = 'ranged';
    case BOTH = 'both';
    case NONE = 'none';
}

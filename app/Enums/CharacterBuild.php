<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterBuild: string
{
    use HasValues;

    case TINY = 'tiny';
    case SLIM = 'slim';
    case AVERAGE = 'average';
    case MUSCULAR = 'muscular';
    case HEAVY = 'heavy';
    case GIANT = 'giant';
}

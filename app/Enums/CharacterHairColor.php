<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterHairColor: string
{
    use HasValues;

    case BLACK = 'black';
    case BROWN = 'brown';
    case BLONDE = 'blonde';
    case RED = 'red';
    case ORANGE = 'orange';
    case WHITE = 'white';
    case GREY = 'grey';
    case BLUE = 'blue';
    case GREEN = 'green';
    case PINK = 'pink';
    case PURPLE = 'purple';
    case MULTICOLOR = 'multicolor';
    case NONE = 'none';
    case OTHER = 'other';
}

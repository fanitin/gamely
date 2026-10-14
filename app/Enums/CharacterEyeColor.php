<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterEyeColor: string
{
    use HasValues;

    case BROWN = 'brown';
    case BLUE = 'blue';
    case GREEN = 'green';
    case BLACK = 'black';
    case GREY = 'grey';
    case RED = 'red';
    case YELLOW = 'yellow';
    case PURPLE = 'purple';
    case PINK = 'pink';
    case WHITE = 'white';
    case HETEROCHROMIA = 'heterochromia';
    case NONE = 'none';
    case OTHER = 'other';
}

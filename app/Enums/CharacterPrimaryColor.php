<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterPrimaryColor: string
{
    use HasValues;

    case RED = 'red';
    case ORANGE = 'orange';
    case YELLOW = 'yellow';
    case GREEN = 'green';
    case BLUE = 'blue';
    case PURPLE = 'purple';
    case PINK = 'pink';
    case BROWN = 'brown';
    case BLACK = 'black';
    case WHITE = 'white';
    case GREY = 'grey';
    case MULTICOLOR = 'multicolor';
}

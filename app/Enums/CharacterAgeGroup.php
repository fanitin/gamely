<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterAgeGroup: string
{
    use HasValues;

    case CHILD = 'child';
    case TEEN = 'teen';
    case YOUNG_ADULT = 'young_adult';
    case ADULT = 'adult';
    case MIDDLE_AGED = 'middle_aged';
    case ELDERLY = 'elderly';
    case AGELESS = 'ageless';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterRole: string
{
    use HasValues;

    case PROTAGONIST = 'protagonist';
    case DEUTERAGONIST = 'deuteragonist';
    case ANTAGONIST = 'antagonist';
    case SUPPORTING = 'supporting';
    case BOSS = 'boss';
    case MINOR = 'minor';
}

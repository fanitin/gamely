<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterHeadwear: string
{
    use HasValues;

    case NONE = 'none';
    case HAT = 'hat';
    case CAP = 'cap';
    case HELMET = 'helmet';
    case MASK = 'mask';
    case CROWN = 'crown';
    case HOOD = 'hood';
    case HEADBAND = 'headband';
    case BANDANA = 'bandana';
    case OTHER = 'other';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterSetting: string
{
    use HasValues;

    case FANTASY = 'fantasy';
    case SCI_FI = 'sci_fi';
    case MODERN = 'modern';
    case HISTORICAL = 'historical';
    case POST_APOCALYPTIC = 'post_apocalyptic';
    case HORROR = 'horror';
    case CARTOON = 'cartoon';
    case MIXED = 'mixed';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CharacterWeaponType: string
{
    use HasValues;

    case SWORD = 'sword';
    case DAGGER = 'dagger';
    case AXE = 'axe';
    case HAMMER = 'hammer';
    case SPEAR = 'spear';
    case BOW = 'bow';
    case GUN = 'gun';
    case EXPLOSIVES = 'explosives';
    case MAGIC = 'magic';
    case STAFF = 'staff';
    case FISTS = 'fists';
    case WHIP = 'whip';
    case SHIELD = 'shield';
    case VEHICLE = 'vehicle';
    case TOOL = 'tool';
    case NONE = 'none';
    case OTHER = 'other';
}

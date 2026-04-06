<?php

namespace App\Enums;

enum GameType: int
{
    case MAIN_GAME = 0;
    case REMAKE = 8;
    case REMASTER = 9;

    public function label(): string
    {
        return match ($this) {
            self::MAIN_GAME => 'Main Game',
            self::REMAKE => 'Remake',
            self::REMASTER => 'Remaster',
        };
    }
}

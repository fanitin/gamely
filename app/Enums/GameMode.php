<?php

namespace App\Enums;

enum GameMode: string
{
    case CLASSIC = "classic";
    case GAME_SCREENSHOTS = "game_screenshots";
    case CHARACTER = "character";

    public function label(): string
    {
        return match ($this) {
            self::CLASSIC => 'Classic',
            self::GAME_SCREENSHOTS => 'Screenshots',
            self::CHARACTER => 'Character',
        };
    }
}

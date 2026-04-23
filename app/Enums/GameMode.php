<?php

namespace App\Enums;

enum GameMode: string
{
    case CLASSIC = "classic";
    case GAME_SCREENSHOTS = "game_screenshots";
    case CHARACTER_ATTRIBUTES = "character_attributes";
    case CHARACTER_IMAGE = "character_image";

    public function label(): string
    {
        return match ($this) {
            self::CLASSIC => 'Classic',
            self::GAME_SCREENSHOTS => 'Screenshots',
            self::CHARACTER_ATTRIBUTES => 'Character Attributes',
            self::CHARACTER_IMAGE => 'Character Image',
        };
    }
}

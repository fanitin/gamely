<?php

namespace App\Enums;

enum GameMode: string
{
    case CLASSIC    = "classic";
    case CHARACTER  = "character";
    case COVER = "cover";
    case SILHOUETTE = "silhouette";

    public function label(): string
    {
        return match ($this) {
            self::CLASSIC => 'Classic',
            self::CHARACTER => 'Character',
            self::COVER => 'Cover',
            self::SILHOUETTE => 'Silhouette',
        };
    }
}

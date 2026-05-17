<?php

namespace App\Enums;

enum PopularityTier: string
{
    case LEGENDARY = 'legendary';
    case POPULAR = 'popular';
    case NOTABLE = 'notable';
    case NICHE = 'niche';

    public function label(): string
    {
        return match ($this) {
            self::LEGENDARY => 'Legendary',
            self::POPULAR => 'Popular',
            self::NOTABLE => 'Notable',
            self::NICHE => 'Niche',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::NICHE => 0,
            self::NOTABLE => 1,
            self::POPULAR => 2,
            self::LEGENDARY => 3,
        };
    }

    public static function fromRatingCount(int $ratingCount, int $hypes = 0): self
    {
        $score = $ratingCount + ($hypes * 2);

        return match (true) {
            $score >= 1500 => self::LEGENDARY,
            $score >= 400 => self::POPULAR,
            $score >= 100 => self::NOTABLE,
            default => self::NICHE,
        };
    }
}

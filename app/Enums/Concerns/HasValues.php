<?php

namespace App\Enums\Concerns;

trait HasValues
{
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public static function tryFromInput(?string $input): ?self
    {
        if ($input === null) {
            return null;
        }

        $normalized = preg_replace('/[\s\-]+/', '_', mb_strtolower(trim($input)));

        if ($normalized === '') {
            return null;
        }

        return self::tryFrom($normalized);
    }
}

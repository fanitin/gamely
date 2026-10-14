<?php

namespace App\Services;

use App\Enums\CharacterAgeGroup;
use App\Enums\CharacterAlignment;
use App\Enums\CharacterBuild;
use App\Enums\CharacterCombatRange;
use App\Enums\CharacterEyeColor;
use App\Enums\CharacterHairColor;
use App\Enums\CharacterHeadwear;
use App\Enums\CharacterOriginMedium;
use App\Enums\CharacterPrimaryColor;
use App\Enums\CharacterRole;
use App\Enums\CharacterSetting;
use App\Enums\CharacterStatus;
use App\Enums\CharacterWeaponType;
use App\Models\Character;
use App\Models\CharacterGender;
use App\Models\CharacterSpecies;
use Illuminate\Support\Str;

class CharacterCsvService
{
    public const LIST_SEPARATOR = '|';

    public const REQUIRED_COLUMNS = ['slug', 'name', 'games'];

    public const COLUMNS = [
        'id', 'igdb_id', 'slug', 'name', 'full_name', 'akas', 'games', 'description',
        'gender', 'species', 'debut_year', 'role', 'alignment', 'hair_color', 'eye_color',
        'weapon_type', 'combat_range', 'age', 'age_group', 'status', 'primary_color', 'build',
        'setting', 'origin_medium', 'headwear', 'height_cm', 'weight_kg', 'occupation',
        'affiliation', 'origin', 'signature_item', 'is_playable', 'is_silent', 'is_mascot',
        'abilities', 'voice_actor', 'creator', 'birthday', 'catchphrase', 'image_url', 'source_url',
    ];

    public const ENUM_COLUMNS = [
        'role' => CharacterRole::class,
        'alignment' => CharacterAlignment::class,
        'hair_color' => CharacterHairColor::class,
        'eye_color' => CharacterEyeColor::class,
        'weapon_type' => CharacterWeaponType::class,
        'combat_range' => CharacterCombatRange::class,
        'age_group' => CharacterAgeGroup::class,
        'status' => CharacterStatus::class,
        'primary_color' => CharacterPrimaryColor::class,
        'build' => CharacterBuild::class,
        'setting' => CharacterSetting::class,
        'origin_medium' => CharacterOriginMedium::class,
        'headwear' => CharacterHeadwear::class,
    ];

    public const INT_COLUMNS = ['debut_year', 'age', 'height_cm', 'weight_kg'];

    public const BOOL_COLUMNS = ['is_playable', 'is_silent', 'is_mascot'];

    public const LIST_COLUMNS = ['akas', 'abilities'];

    public const TEXT_COLUMNS = [
        'name', 'full_name', 'description', 'occupation', 'affiliation', 'origin', 'signature_item',
        'voice_actor', 'creator', 'birthday', 'catchphrase', 'image_url', 'source_url',
    ];

    public const GENDERS = ['male', 'female', 'other', 'none'];

    public const SPECIES = [
        'human', 'elf', 'dwarf', 'orc', 'demon', 'angel', 'deity', 'android', 'alien', 'animal',
        'anthro', 'monster', 'undead', 'dragon', 'spirit', 'mutant', 'unknown', 'other',
    ];

    private array $genderCache = [];

    private array $speciesCache = [];

    public function headers(): array
    {
        return self::COLUMNS;
    }

    public function vocabularies(): array
    {
        $vocabularies = [
            'gender' => self::GENDERS,
            'species' => self::SPECIES,
        ];

        foreach (self::ENUM_COLUMNS as $column => $enum) {
            $vocabularies[$column] = $enum::values();
        }

        return $vocabularies;
    }

    public function toRow(Character $character): array
    {
        $row = [];

        foreach (self::COLUMNS as $column) {
            $row[$column] = match (true) {
                $column === 'id' => $character->id,
                $column === 'igdb_id' => $character->igdb_id,
                $column === 'games' => $character->games->pluck('igdb_id')->filter()->implode(self::LIST_SEPARATOR),
                $column === 'gender' => $this->lookupValue($character->gender?->name),
                $column === 'species' => $this->lookupValue($character->species?->name),
                isset(self::ENUM_COLUMNS[$column]) => $character->{$column}?->value,
                in_array($column, self::BOOL_COLUMNS, true) => $this->boolToCsv($character->{$column}),
                in_array($column, self::LIST_COLUMNS, true) => implode(self::LIST_SEPARATOR, $character->{$column} ?? []),
                default => $character->{$column},
            };
        }

        return $row;
    }

    public function parseRow(array $row): array
    {
        $attributes = [];
        $warnings = [];

        foreach ($row as $column => $raw) {
            $value = is_string($raw) ? trim($raw) : $raw;

            if ($value === null || $value === '' || in_array($column, ['id', 'igdb_id', 'slug', 'games'], true)) {
                continue;
            }

            if ($column === 'gender') {
                $genderId = $this->resolveGender($value);
                $genderId ? $attributes['gender_id'] = $genderId : $warnings[] = "gender '{$value}' is not in vocabulary";

                continue;
            }

            if ($column === 'species') {
                $speciesId = $this->resolveSpecies($value);
                $speciesId ? $attributes['species_id'] = $speciesId : $warnings[] = "species '{$value}' is not in vocabulary";

                continue;
            }

            if (isset(self::ENUM_COLUMNS[$column])) {
                $enum = self::ENUM_COLUMNS[$column]::tryFromInput($value);
                $enum ? $attributes[$column] = $enum : $warnings[] = "{$column} '{$value}' is not in vocabulary";

                continue;
            }

            if (in_array($column, self::INT_COLUMNS, true)) {
                is_numeric($value) ? $attributes[$column] = (int) $value : $warnings[] = "{$column} '{$value}' is not a number";

                continue;
            }

            if (in_array($column, self::BOOL_COLUMNS, true)) {
                $bool = $this->csvToBool($value);
                $bool === null ? $warnings[] = "{$column} '{$value}' is not a boolean" : $attributes[$column] = $bool;

                continue;
            }

            if (in_array($column, self::LIST_COLUMNS, true)) {
                $attributes[$column] = $this->splitList($value);

                continue;
            }

            if (in_array($column, self::TEXT_COLUMNS, true)) {
                $attributes[$column] = $value;
            }
        }

        return [
            'id' => $this->intOrNull($row['id'] ?? null),
            'igdb_id' => $this->intOrNull($row['igdb_id'] ?? null),
            'slug' => $this->slugOrNull($row['slug'] ?? null, $attributes['name'] ?? null),
            'game_igdb_ids' => array_map('intval', $this->splitList($row['games'] ?? '')),
            'attributes' => $attributes,
            'warnings' => $warnings,
        ];
    }

    private function resolveGender(string $value): ?int
    {
        $key = $this->lookupValue($value);

        if (! in_array($key, self::GENDERS, true)) {
            return null;
        }

        return $this->genderCache[$key] ??= CharacterGender::firstOrCreate(['name' => Str::title($key)])->id;
    }

    private function resolveSpecies(string $value): ?int
    {
        $key = $this->lookupValue($value);

        if (! in_array($key, self::SPECIES, true)) {
            return null;
        }

        return $this->speciesCache[$key] ??= CharacterSpecies::firstOrCreate(['name' => Str::title($key)])->id;
    }

    private function lookupValue(?string $name): ?string
    {
        return $name === null ? null : mb_strtolower(trim($name));
    }

    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(self::LIST_SEPARATOR, $value)), fn ($v) => $v !== ''));
    }

    private function boolToCsv(?bool $value): string
    {
        return $value === null ? '' : ($value ? '1' : '0');
    }

    private function csvToBool(string $value): ?bool
    {
        return match (mb_strtolower($value)) {
            '1', 'true', 'yes', 'y' => true,
            '0', 'false', 'no', 'n' => false,
            default => null,
        };
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function slugOrNull(mixed $slug, ?string $name): ?string
    {
        $slug = is_string($slug) ? Str::slug(trim($slug)) : '';

        if ($slug !== '') {
            return $slug;
        }

        return $name ? Str::slug($name) : null;
    }
}

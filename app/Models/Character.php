<?php

namespace App\Models;

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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection as SupportCollection;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Character extends Model
{
    use HasFactory;
    protected $fillable = [
        'igdb_id',
        'name',
        'full_name',
        'slug',
        'description',
        'country_name',
        'akas',
        'mug_shot_igdb_id',
        'mug_shot_url',
        'gender_id',
        'species_id',
        'is_active',
        'debut_year',
        'role',
        'alignment',
        'hair_color',
        'eye_color',
        'weapon_type',
        'combat_range',
        'age_group',
        'status',
        'primary_color',
        'build',
        'setting',
        'origin_medium',
        'headwear',
        'age',
        'height_cm',
        'weight_kg',
        'occupation',
        'affiliation',
        'origin',
        'signature_item',
        'is_playable',
        'is_silent',
        'is_mascot',
        'abilities',
        'voice_actor',
        'creator',
        'birthday',
        'catchphrase',
        'image_url',
        'source_url',
    ];

    protected $casts = [
        'akas' => 'array',
        'abilities' => 'array',
        'is_active' => 'boolean',
        'is_playable' => 'boolean',
        'is_silent' => 'boolean',
        'is_mascot' => 'boolean',
        'debut_year' => 'integer',
        'age' => 'integer',
        'height_cm' => 'integer',
        'weight_kg' => 'integer',
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

    public function gender(): BelongsTo
    {
        return $this->belongsTo(CharacterGender::class, 'gender_id');
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(CharacterSpecies::class, 'species_id');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'character_game');
    }

    public function franchises(): SupportCollection
    {
        $gameIds = $this->games()->pluck('games.id');

        if ($gameIds->isEmpty()) {
            return collect();
        }

        return Franchise::whereHas('games', function ($query) use ($gameIds) {
            $query->whereIn('games.id', $gameIds);
        })->distinct()->get();
    }

    public function collections(): SupportCollection
    {
        $gameIds = $this->games()->pluck('games.id');

        if ($gameIds->isEmpty()) {
            return collect();
        }

        return Collection::whereHas('games', function ($query) use ($gameIds) {
            $query->whereIn('games.id', $gameIds);
        })->distinct()->get();
    }

    public function firstAppearanceYear(): ?int
    {
        if ($this->debut_year) {
            return $this->debut_year;
        }

        $earliestGame = $this->games()
            ->whereNotNull('release_date')
            ->orderBy('release_date', 'asc')
            ->first();

        if (!$earliestGame || !$earliestGame->release_date) {
            return null;
        }

        return (int) substr($earliestGame->release_date, 0, 4);
    }

    public function firstAppearanceGame()
    {
        $game = $this->games()
            ->whereNotNull('release_date')
            ->orderBy('release_date', 'asc')
            ->first();

        return $game;
    }
}

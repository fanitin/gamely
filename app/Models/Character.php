<?php

namespace App\Models;

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
        'slug',
        'description',
        'country_name',
        'akas',
        'mug_shot_igdb_id',
        'mug_shot_url',
        'gender_id',
        'species_id',
        'is_active',
    ];

    protected $casts = [
        'akas' => 'array',
        'is_active' => 'boolean',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
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
}

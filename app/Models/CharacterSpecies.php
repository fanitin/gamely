<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CharacterSpecies extends Model
{
    use HasFactory;
    protected $fillable = [
        'igdb_id',
        'name',
    ];

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class, 'species_id');
    }
}

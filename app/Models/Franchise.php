<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Franchise extends Model
{
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_franchise');
    }
}

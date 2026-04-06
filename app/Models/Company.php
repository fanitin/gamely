<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
        'logo_url',
    ];

    public function developedGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_developer');
    }

    public function publishedGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_publisher');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collection extends Model
{
    use HasFactory;
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_collection');
    }
}

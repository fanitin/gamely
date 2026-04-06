<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}

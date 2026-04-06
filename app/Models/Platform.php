<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'igdb_id',
        'slug',
        'abbreviation',
        'platform_logo_id',
        'platform_family_id',
        'url',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
    }

    public function platformFamily(): BelongsTo
    {
        return $this->belongsTo(PlatformFamily::class);
    }
}

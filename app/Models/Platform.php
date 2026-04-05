<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function platformFamily(): BelongsTo
    {
        return $this->belongsTo(PlatformFamily::class);
    }
}

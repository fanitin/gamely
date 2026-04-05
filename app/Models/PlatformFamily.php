<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformFamily extends Model
{
    protected $fillable = [
        'name',
        'igdb_id',
        'slug',
    ];

    public function platforms(): HasMany
    {
        return $this->hasMany(Platform::class);
    }
}

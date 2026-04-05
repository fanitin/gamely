<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerPerspective extends Model
{
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
    ];
}

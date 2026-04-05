<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMode extends Model
{
    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
    ];
}

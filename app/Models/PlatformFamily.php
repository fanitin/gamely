<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFamily extends Model
{
    protected $fillable = [
        'name',
        'igdb_id',
        'slug',
    ];
}

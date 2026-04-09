<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameScreenshot extends Model
{
    protected $fillable = [
        'game_id',
        'url',
        'order',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}

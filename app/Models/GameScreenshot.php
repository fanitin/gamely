<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GameScreenshot extends Model
{
    protected $fillable = [
        'game_id',
        'url',
        'order',
    ];

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Storage::disk('r2')->url($value) : null,
        );
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}

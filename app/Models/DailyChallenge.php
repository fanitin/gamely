<?php

namespace App\Models;

use App\Enums\GameMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyChallenge extends Model
{
    protected $fillable = [
        'mode',
        'date',
        'game_id',
        'character_id',
    ];

    protected $casts = [
        'mode' => GameMode::class,
        'date' => 'date',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'challenge_id');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(SiteStats::class, 'challenge_id');
    }

    public function getEntity(): Model|null
    {
        return match ($this->mode) {
            GameMode::CLASSIC, GameMode::COVER => $this->game,
            GameMode::CHARACTER, GameMode::SILHOUETTE => $this->character,
        };
    }

    public function scopeForMode($query, GameMode $mode)
    {
        return $query->where('mode', $mode->value);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }
}

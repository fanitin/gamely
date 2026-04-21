<?php

namespace App\Models;

use App\Enums\GameMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSession extends Model
{
    protected $fillable = [
        'mode',
        'session_token',
        'challenge_id',
        'attempts',
        'completed_at',
    ];

    protected $casts = [
        'mode' => GameMode::class,
        'completed_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(DailyChallenge::class, 'challenge_id');
    }


    public function scopeForMode($query, GameMode $mode)
    {
        return $query->where('mode', $mode->value);
    }

    public function scopeForChallenge($query, int $challengeId)
    {
        return $query->where('challenge_id', $challengeId);
    }

    public function scopeForSession($query, string $sessionToken)
    {
        return $query->where('session_token', $sessionToken);
    }

    public function scopeInfiniteMode($query)
    {
        return $query->whereNull('challenge_id');
    }
}

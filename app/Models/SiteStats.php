<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteStats extends Model
{
    protected $fillable = [
        'challenge_id',
        'total_players',
        'attempts_distribution',
        'calculated_at',
    ];

    protected $casts = [
        'attempts_distribution' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(DailyChallenge::class, 'challenge_id');
    }

    public static function incrementStats(GameSession $session): ?self
    {
        if (!$session->challenge_id) {
            return null;
        }

        $stats = self::firstOrCreate(
            ['challenge_id' => $session->challenge_id],
            [
                'total_players' => 0,
                'attempts_distribution' => [],
            ]
        );

        $isFirstAttempt = GameSession::where('challenge_id', $session->challenge_id)
            ->where('session_token', $session->session_token)
            ->count() === 1;

        if ($isFirstAttempt) {
            $stats->increment('total_players');
        }

        $distribution = $stats->attempts_distribution ?? [];
        $attempts = (string)$session->attempts;
        $distribution[$attempts] = ($distribution[$attempts] ?? 0) + 1;
        $stats->attempts_distribution = $distribution;

        $stats->calculated_at = now();
        $stats->save();

        return $stats;
    }
}

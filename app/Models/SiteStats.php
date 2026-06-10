<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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

        return DB::transaction(function () use ($session) {
            $stats = self::query()
                ->where('challenge_id', $session->challenge_id)
                ->lockForUpdate()
                ->first();

            if (! $stats) {
                self::createOrFirst(
                    ['challenge_id' => $session->challenge_id],
                    [
                        'total_players' => 0,
                        'attempts_distribution' => [],
                    ]
                );

                $stats = self::query()
                    ->where('challenge_id', $session->challenge_id)
                    ->lockForUpdate()
                    ->first();
            }

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
        });
    }
}

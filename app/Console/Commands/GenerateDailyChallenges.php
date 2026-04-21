<?php

namespace App\Console\Commands;

use App\Enums\GameMode;
use App\Models\DailyChallenge;
use App\Models\Game;
use App\Models\Character;
use Illuminate\Console\Command;

class GenerateDailyChallenges extends Command
{
    protected $signature = 'challenges:generate {date?}';

    protected $description = 'Generate daily challenges for specified date (tomorrow by default)';

    public function handle(): void
    {
        $date = $this->argument('date') ?? today()->toDateString();

        $this->info("Generating challenges for {$date}...");

        foreach (GameMode::cases() as $mode) {
            if (DailyChallenge::where('mode', $mode->value)->where('date', $date)->exists()) {
                $this->warn("Challenge for {$mode->value} mode on {$date} already exists");
                continue;
            }

            $challenge = $this->generateChallenge($mode, $date);

            if ($challenge) {
                $entityInfo = '';
                if ($mode === GameMode::CLASSIC) {
                    $entityInfo = " (game #{$challenge->game_id})";
                } elseif ($mode === GameMode::CHARACTER) {
                    $entityInfo = " (character #{$challenge->character_id})";
                } elseif ($mode === GameMode::COVER) {
                    $entityInfo = " (game #{$challenge->game_id} with cover)";
                } elseif ($mode === GameMode::SILHOUETTE) {
                    $entityInfo = " (character #{$challenge->character_id} with mug_shot)";
                }

                $this->info("✓ {$mode->label()}{$entityInfo}");
            } else {
                $this->error("✗ Failed to generate challenge for {$mode->value}");
            }
        }

        $this->info("\nDone!");
    }

    private function generateChallenge(GameMode $mode, string $date): ?DailyChallenge
    {
        return match ($mode) {
            GameMode::CLASSIC => $this->generateClassicChallenge($date),
            GameMode::CHARACTER => $this->generateCharacterChallenge($date),
            GameMode::COVER => $this->generateCoverChallenge($date),
            GameMode::SILHOUETTE => $this->generateSilhouetteChallenge($date),
        };
    }

    private function generateClassicChallenge(string $date): DailyChallenge
    {
        $seed = crc32($date);
        $game = Game::inRandomOrder($seed)->first();

        return DailyChallenge::create([
            'mode' => GameMode::CLASSIC->value,
            'date' => $date,
            'game_id' => $game->id,
        ]);
    }

    private function generateCharacterChallenge(string $date): ?DailyChallenge
    {
        $seed = crc32($date);
        $character = Character::has('games')
            ->whereNotNull('gender_id')
            ->whereNotNull('species_id')
            ->inRandomOrder($seed)
            ->first();

        if (!$character) {
            return null;
        }

        return DailyChallenge::create([
            'mode' => GameMode::CHARACTER->value,
            'date' => $date,
            'character_id' => $character->id,
        ]);
    }

    private function generateCoverChallenge(string $date): ?DailyChallenge
    {
        $seed = crc32($date);
        $game = Game::whereNotNull('cover_url')->inRandomOrder($seed)->first();

        if (!$game) {
            return null;
        }

        return DailyChallenge::create([
            'mode' => GameMode::COVER->value,
            'date' => $date,
            'game_id' => $game->id,
        ]);
    }

    private function generateSilhouetteChallenge(string $date): ?DailyChallenge
    {
        $seed = crc32($date);
        $character = Character::whereNotNull('mug_shot_url')
            ->whereNotNull('gender_id')
            ->whereNotNull('species_id')
            ->inRandomOrder($seed)
            ->first();

        if (!$character) {
            return null;
        }

        return DailyChallenge::create([
            'mode' => GameMode::SILHOUETTE->value,
            'date' => $date,
            'character_id' => $character->id,
        ]);
    }
}

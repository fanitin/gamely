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
                } elseif ($mode === GameMode::GAME_SCREENSHOTS) {
                    $entityInfo = " (game #{$challenge->game_id} with screenshots)";
                } elseif ($mode === GameMode::CHARACTER_ATTRIBUTES) {
                    $entityInfo = " (character #{$challenge->character_id} by attributes)";
                } elseif ($mode === GameMode::CHARACTER_IMAGE) {
                    $entityInfo = " (character #{$challenge->character_id} by mugshot)";
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
            GameMode::GAME_SCREENSHOTS => $this->generateScreenshotsChallenge($date),
            GameMode::CHARACTER_ATTRIBUTES => $this->generateCharacterAttributesChallenge($date),
            GameMode::CHARACTER_IMAGE => $this->generateCharacterImageChallenge($date),
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

    private function generateScreenshotsChallenge(string $date): ?DailyChallenge
    {
        $seed = crc32($date);
        $game = Game::has('screenshots')->inRandomOrder($seed)->first();

        if (!$game) {
            return null;
        }

        return DailyChallenge::create([
            'mode' => GameMode::GAME_SCREENSHOTS->value,
            'date' => $date,
            'game_id' => $game->id,
        ]);
    }

    private function generateCharacterAttributesChallenge(string $date): ?DailyChallenge
    {
        $seed = crc32($date);
        $character = Character::whereNotNull('gender_id')
            ->whereNotNull('species_id')
            ->inRandomOrder($seed)
            ->first();

        if (!$character) {
            return null;
        }

        return DailyChallenge::create([
            'mode' => GameMode::CHARACTER_ATTRIBUTES->value,
            'date' => $date,
            'character_id' => $character->id,
        ]);
    }

    private function generateCharacterImageChallenge(string $date): ?DailyChallenge
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
            'mode' => GameMode::CHARACTER_IMAGE->value,
            'date' => $date,
            'character_id' => $character->id,
        ]);
    }
}

<?php

namespace App\Services;

use App\Enums\GameMode;
use App\Models\DailyChallenge;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Character;
use Illuminate\Support\Str;

class GuessService
{
    public function __construct(
        private GameComparisonService $comparisonService
    ) {}

    public function makeGuess(
        int $entityId,
        GameMode $mode,
        string $sessionToken,
        ?string $date = null
    ): array {
        $date = $date ?? today()->toDateString();

        $challenge = DailyChallenge::forMode($mode)
            ->forDate($date)
            ->with([
                'game.genres',
                'game.platforms',
                'game.developers',
                'game.publishers',
                'game.franchises',
                'game.collections',
                'game.gameModes',
                'game.playerPerspectives',
                'character.games.franchises',
                'character.games.collections',
                'character.gender',
                'character.species'
            ])
            ->firstOrFail();

        $targetEntity = $challenge->getEntity();

        if (!$targetEntity) {
            return [
                'success' => false,
                'error' => 'Challenge entity not found',
            ];
        }

        $comparison = match ($mode) {
            GameMode::CLASSIC, GameMode::GAME_SCREENSHOTS => $this->compareGameGuess($entityId, $targetEntity->id, $targetEntity),
            GameMode::CHARACTER_ATTRIBUTES => $this->compareCharacterGuess($entityId, $targetEntity->id, $targetEntity),
            GameMode::CHARACTER_IMAGE => $this->compareSimpleGuess($entityId, $targetEntity->id, $mode),
        };

        $isCorrect = $comparison['is_correct'];

        if ($isCorrect) {
            $alreadyCompleted = GameSession::where('challenge_id', $challenge->id)
                ->where('session_token', $sessionToken)
                ->exists();

            if ($alreadyCompleted) {
                return [
                    'success' => false,
                    'error' => 'Already completed this challenge',
                ];
            }

            $attempts = $this->countAttempts($challenge->id, $sessionToken) + 1;

            $session = GameSession::create([
                'mode' => $mode,
                'session_token' => $sessionToken,
                'challenge_id' => $challenge->id,
                'attempts' => $attempts,
                'completed_at' => now(),
            ]);

            return [
                'success' => true,
                'is_correct' => true,
                'attempts' => $attempts,
                'session_id' => $session->id,
                'comparison' => $comparison['data'] ?? null,
            ];
        }

        return [
            'success' => true,
            'is_correct' => false,
            'comparison' => $comparison['data'] ?? null,
        ];
    }

    private function compareGameGuess(int $guessedId, int $targetId, Game $targetGame): array
    {
        $guessedGame = Game::with([
            'genres',
            'platforms',
            'developers',
            'publishers',
            'franchises',
            'collections',
            'gameModes',
            'playerPerspectives'
        ])->findOrFail($guessedId);

        $comparison = $this->comparisonService->compareGames($guessedGame, $targetGame);

        return [
            'is_correct' => $guessedId === $targetId,
            'data' => [
                'guessed' => [
                    'id' => $guessedGame->id,
                    'name' => $guessedGame->name,
                    'display_name' => $guessedGame->display_name,
                    'cover_url' => $guessedGame->cover_url,
                    'release_year' => $guessedGame->release_date ? substr($guessedGame->release_date, 0, 4) : null,
                    'rating' => $guessedGame->rating,
                    'genres' => $guessedGame->genres->map(fn($g) => ['id' => $g->id, 'name' => $g->name]),
                    'platforms' => $guessedGame->platforms->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'url' => $p->url
                    ]),
                    'developers' => $guessedGame->developers->map(fn($d) => ['id' => $d->id, 'name' => $d->name]),
                    'publishers' => $guessedGame->publishers->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
                    'franchises' => $guessedGame->franchises->map(fn($f) => ['id' => $f->id, 'name' => $f->name]),
                    'collections' => $guessedGame->collections->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                    'game_modes' => $guessedGame->gameModes->map(fn($m) => ['id' => $m->id, 'name' => $m->name]),
                    'player_perspectives' => $guessedGame->playerPerspectives->map(fn($p) => ['id' => $p->id, 'name' => $p->name]),
                ],
                'comparison' => $comparison,
            ],
        ];
    }

    private function compareCharacterGuess(int $guessedId, int $targetId, Character $targetChar): array
    {
        $guessedChar = Character::with(['games.franchises', 'games.collections', 'gender', 'species'])
            ->findOrFail($guessedId);

        $comparison = $this->comparisonService->compareCharacters($guessedChar, $targetChar);

        return [
            'is_correct' => $guessedId === $targetId,
            'data' => [
                'guessed' => [
                    'id' => $guessedChar->id,
                    'name' => $guessedChar->name,
                    'mug_shot_url' => $guessedChar->mug_shot_url,
                    'gender' => $guessedChar->gender?->name,
                    'species' => $guessedChar->species?->name,
                    'first_appearance_year' => $guessedChar->firstAppearanceYear(),
                    'franchises' => $guessedChar->franchises()->map(fn($f) => ['id' => $f->id, 'name' => $f->name]),
                    'collections' => $guessedChar->collections()->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                ],
                'comparison' => $comparison,
            ],
        ];
    }

    private function compareSimpleGuess(int $guessedId, int $targetId, GameMode $mode): array
    {
        return [
            'is_correct' => $guessedId === $targetId,
        ];
    }

    private function countAttempts(int $challengeId, string $sessionToken): int
    {
        return GameSession::where('challenge_id', $challengeId)
            ->where('session_token', $sessionToken)
            ->count();
    }
}

<?php

namespace Tests\Feature;

use App\Enums\GameMode;
use App\Models\DailyChallenge;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChallengeScreenshotsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_screenshots_challenge_returns_platforms_hint_instead_of_year_hint(): void
    {
        Storage::fake('r2');

        $game = Game::factory()->create([
            'release_date' => '2020-01-01',
        ]);

        $platformA = Platform::create([
            'name' => 'PC',
            'slug' => 'pc',
            'igdb_id' => 6,
        ]);
        $platformB = Platform::create([
            'name' => 'PlayStation 5',
            'slug' => 'playstation-5',
            'igdb_id' => 167,
        ]);
        $genre = Genre::create([
            'name' => 'RPG',
            'slug' => 'rpg',
            'igdb_id' => 12,
        ]);

        $game->platforms()->attach([$platformA->id, $platformB->id]);
        $game->genres()->attach([$genre->id]);
        $game->screenshots()->create([
            'url' => 'screenshots/test.webp',
            'order' => 1,
        ]);

        DailyChallenge::create([
            'mode' => GameMode::GAME_SCREENSHOTS,
            'date' => today()->toDateString(),
            'game_id' => $game->id,
        ]);

        $response = $this->getJson(route('api.challenge.screenshots'));

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('hints.platforms.value', 'PC, PlayStation 5')
            ->assertJsonPath('hints.platforms.unlock_at', 4)
            ->assertJsonPath('hints.genre.value', 'RPG')
            ->assertJsonMissingPath('hints.year');
    }
}

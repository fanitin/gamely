<?php

namespace App\Console\Commands\IGDB;

use App\Models\Character;
use App\Models\CharacterGender;
use App\Models\CharacterSpecies;
use App\Models\Game;
use App\Services\CharacterQualityService;
use App\Services\IgdbService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-characters')]
#[Description('Import characters from IGDB API (skips junk names and characters without popular games)')]
class ImportCharacters extends AbstractIgdbImport
{
    private array $genderMap = [];

    private array $speciesMap = [];

    public function __construct(IgdbService $igdb, private CharacterQualityService $quality)
    {
        parent::__construct($igdb);
    }

    public function handle(): int
    {
        $this->genderMap = CharacterGender::pluck('id', 'igdb_id')->toArray();
        $this->speciesMap = CharacterSpecies::pluck('id', 'igdb_id')->toArray();

        return parent::handle();
    }

    protected function getEndpoint(): string
    {
        return 'characters';
    }

    protected function getModelClass(): string
    {
        return Character::class;
    }

    protected function getQueryBody(): string
    {
        return 'fields akas,character_gender,character_species,country_name,description,games,mug_shot,name,slug; where games.game_type = (0,8,9) & (games.rating > 70 | games.rating_count > 100) & games.first_release_date != null;';
    }

    protected function processItem(array $item): string
    {
        if (empty($item['games']) || ! is_array($item['games'])) {
            return 'skipped';
        }

        $existingGames = Game::whereIn('igdb_id', $item['games'])->get(['id', 'popularity_tier']);

        if ($existingGames->isEmpty()) {
            return 'skipped';
        }

        $hasMugShot = ! empty($item['mug_shot']);

        if (! $hasMugShot && ($this->quality->isJunk($item['name'] ?? '') || ! $this->quality->hasPopularGame($existingGames))) {
            return 'skipped';
        }

        $existingGameIds = $existingGames->pluck('id')->toArray();

        $character = Character::updateOrCreate(
            ['igdb_id' => $item['id']],
            [
                'name' => $item['name'] ?? null,
                'slug' => $item['slug'] ?? null,
                'description' => $item['description'] ?? null,
                'country_name' => $item['country_name'] ?? null,
                'akas' => $item['akas'] ?? null,
                'gender_id' => isset($item['character_gender']) ? ($this->genderMap[$item['character_gender']] ?? null) : null,
                'species_id' => isset($item['character_species']) ? ($this->speciesMap[$item['character_species']] ?? null) : null,
                'mug_shot_igdb_id' => $item['mug_shot'] ?? null,
            ]
        );

        $character->games()->syncWithoutDetaching($existingGameIds);

        if ($character->wasRecentlyCreated) {
            return 'created';
        }

        if ($character->wasChanged()) {
            return 'updated';
        }

        return 'skipped';
    }
}

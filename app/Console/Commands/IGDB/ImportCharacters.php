<?php

namespace App\Console\Commands\IGDB;

use App\Models\Character;
use App\Models\CharacterGender;
use App\Models\CharacterSpecies;
use App\Models\Game;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('import:igdb-characters')]
#[Description('Import characters from IGDB API')]
class ImportCharacters extends AbstractIgdbImport
{
    private array $genderMap = [];

    private array $speciesMap = [];

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

    protected function getQueryBody(): string
    {
        return 'fields akas,character_gender,character_species,country_name,description,games,mug_shot,name,slug; where games.game_type = (0,8,9) & games.rating_count >= 20 & games.rating > 60 & games.first_release_date != null;';
    }

    protected function processItem(array $item): string
    {
        if (empty($item['games']) || ! is_array($item['games'])) {
            return 'skipped';
        }

        $existingGameIds = Game::whereIn('igdb_id', $item['games'])->pluck('id')->toArray();

        if (empty($existingGameIds)) {
            return 'skipped';
        }

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

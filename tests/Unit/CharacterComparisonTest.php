<?php

namespace Tests\Unit;

use App\Models\Character;
use App\Models\CharacterGender;
use App\Models\CharacterSpecies;
use App\Models\Game;
use App\Models\Franchise;
use App\Models\Collection;
use App\Services\GameComparisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterComparisonTest extends TestCase
{
    use RefreshDatabase;

    private GameComparisonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GameComparisonService::class);
    }

    public function test_compare_characters_exact_match()
    {
        $gender = CharacterGender::factory()->create();
        $species = CharacterSpecies::factory()->create();

        $char = Character::factory()->create([
            'gender_id' => $gender->id,
            'species_id' => $species->id,
        ]);

        $result = $this->service->compareCharacters($char, $char);

        $this->assertEquals('exact', $result['gender']['result']);
        $this->assertEquals('exact', $result['species']['result']);
    }

    public function test_compare_first_appearance_year_exact()
    {
        $char1 = Character::factory()->create();
        $char2 = Character::factory()->create();

        $game1 = Game::factory()->create(['release_date' => '2020-01-01']);
        $game2 = Game::factory()->create(['release_date' => '2020-06-15']);

        $char1->games()->attach($game1->id);
        $char2->games()->attach($game2->id);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('exact', $result['first_appearance_year']['result']);
        $this->assertEquals(2020, $result['first_appearance_year']['value']);
    }

    public function test_compare_first_appearance_year_close()
    {
        $char1 = Character::factory()->create();
        $char2 = Character::factory()->create();

        $game1 = Game::factory()->create(['release_date' => '2020-01-01']);
        $game2 = Game::factory()->create(['release_date' => '2022-01-01']);

        $char1->games()->attach($game1->id);
        $char2->games()->attach($game2->id);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('close', $result['first_appearance_year']['result']);
        $this->assertEquals('up', $result['first_appearance_year']['arrow']);
    }

    public function test_compare_first_appearance_year_wrong()
    {
        $char1 = Character::factory()->create();
        $char2 = Character::factory()->create();

        $game1 = Game::factory()->create(['release_date' => '2010-01-01']);
        $game2 = Game::factory()->create(['release_date' => '2020-01-01']);

        $char1->games()->attach($game1->id);
        $char2->games()->attach($game2->id);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('wrong', $result['first_appearance_year']['result']);
        $this->assertEquals('up', $result['first_appearance_year']['arrow']);
    }

    public function test_compare_franchises_exact_match()
    {
        $char1 = Character::factory()->create();
        $char2 = Character::factory()->create();

        $franchise = Franchise::factory()->create();
        $game1 = Game::factory()->create();
        $game2 = Game::factory()->create();

        $game1->franchises()->attach($franchise->id);
        $game2->franchises()->attach($franchise->id);

        $char1->games()->attach($game1->id);
        $char2->games()->attach($game2->id);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('exact', $result['franchises']['result']);
    }

    public function test_compare_franchises_close_match()
    {
        $char1 = Character::factory()->create();
        $char2 = Character::factory()->create();

        $franchise1 = Franchise::factory()->create();
        $franchise2 = Franchise::factory()->create();

        $game1 = Game::factory()->create();
        $game2 = Game::factory()->create();

        $game1->franchises()->attach([$franchise1->id, $franchise2->id]);
        $game2->franchises()->attach($franchise1->id);

        $char1->games()->attach($game1->id);
        $char2->games()->attach($game2->id);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('close', $result['franchises']['result']);
    }

    public function test_compare_gender_null_handling()
    {
        $char1 = Character::factory()->create(['gender_id' => null]);
        $char2 = Character::factory()->create(['gender_id' => null]);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('exact', $result['gender']['result']);
    }

    public function test_compare_species_null_handling()
    {
        $char1 = Character::factory()->create(['species_id' => null]);
        $char2 = Character::factory()->create(['species_id' => null]);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('exact', $result['species']['result']);
    }

    public function test_compare_collections_exact_match()
    {
        $char1 = Character::factory()->create();
        $char2 = Character::factory()->create();

        $collection = Collection::factory()->create();
        $game1 = Game::factory()->create();
        $game2 = Game::factory()->create();

        $game1->collections()->attach($collection->id);
        $game2->collections()->attach($collection->id);

        $char1->games()->attach($game1->id);
        $char2->games()->attach($game2->id);

        $result = $this->service->compareCharacters($char1, $char2);

        $this->assertEquals('exact', $result['collections']['result']);
    }
}

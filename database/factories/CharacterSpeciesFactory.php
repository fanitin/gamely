<?php

namespace Database\Factories;

use App\Models\CharacterSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterSpecies>
 */
class CharacterSpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'igdb_id' => $this->faker->unique()->numberBetween(1, 100000),
            'name' => $this->faker->word(),
        ];
    }
}

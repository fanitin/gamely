<?php

namespace Database\Factories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Character>
 */
class CharacterFactory extends Factory
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
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'is_active' => true,
        ];
    }
}

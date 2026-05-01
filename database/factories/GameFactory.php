<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
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
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'is_active' => true,
        ];
    }
}

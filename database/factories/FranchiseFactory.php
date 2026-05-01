<?php

namespace Database\Factories;

use App\Models\Franchise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Franchise>
 */
class FranchiseFactory extends Factory
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
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
        ];
    }
}

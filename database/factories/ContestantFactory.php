<?php

namespace Database\Factories;

use App\Models\Bracket;
use App\Models\Contestant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Contestant> */
class ContestantFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'bracket_id' => Bracket::factory(),
            'name' => fake()->unique()->words(2, true),
            'image_path' => null,
            'seed' => fake()->numberBetween(1, 64),
        ];
    }
}

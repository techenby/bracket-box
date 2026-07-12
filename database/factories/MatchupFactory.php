<?php

namespace Database\Factories;

use App\Models\Bracket;
use App\Models\Matchup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Matchup> */
class MatchupFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'bracket_id' => Bracket::factory(),
            'round' => 1,
            'position' => fake()->unique()->numberBetween(0, 1000),
            'decided_by_coin_flip' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'opens_at' => now()->subDays(2),
            'closes_at' => now()->subDay(),
        ]);
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ]);
    }
}

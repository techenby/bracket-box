<?php

namespace Database\Factories;

use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Bracket> */
class BracketFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'slug' => fn (array $attributes): string => Str::slug($attributes['name']).'-'.Str::lower(Str::random(6)),
            'description' => fake()->sentence(),
            'size' => fake()->randomElement([4, 8, 16, 32, 64]),
            'status' => BracketStatus::Draft,
            'round_duration_hours' => fake()->randomElement([6, 12, 24, 48]),
            'is_unlisted' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => BracketStatus::Active,
            'current_round' => 1,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => BracketStatus::Completed,
            'current_round' => null,
            'completed_at' => now(),
        ]);
    }

    public function unlisted(): static
    {
        return $this->state(fn () => [
            'is_unlisted' => true,
        ]);
    }
}

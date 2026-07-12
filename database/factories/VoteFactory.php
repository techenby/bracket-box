<?php

namespace Database\Factories;

use App\Models\Contestant;
use App\Models\Matchup;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Vote> */
class VoteFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'matchup_id' => Matchup::factory(),
            'contestant_id' => Contestant::factory(),
            'voter_hash' => hash('sha256', Str::random(40)),
            'user_id' => null,
        ];
    }
}

<?php

use App\Models\Bracket;
use App\Models\Contestant;
use App\Models\Matchup;

it('returns contestants ordered by seed', function () {
    $bracket = Bracket::factory()->create();
    Contestant::factory()->for($bracket)->create(['seed' => 3]);
    Contestant::factory()->for($bracket)->create(['seed' => 1]);
    Contestant::factory()->for($bracket)->create(['seed' => 2]);

    expect($bracket->contestants->pluck('seed')->all())->toBe([1, 2, 3]);
});

it('returns matchups ordered by round then position', function () {
    $bracket = Bracket::factory()->create();
    Matchup::factory()->for($bracket)->create(['round' => 2, 'position' => 0]);
    Matchup::factory()->for($bracket)->create(['round' => 1, 'position' => 1]);
    Matchup::factory()->for($bracket)->create(['round' => 1, 'position' => 0]);

    expect($bracket->matchups->map(fn (Matchup $matchup) => [$matchup->round, $matchup->position])->all())
        ->toBe([[1, 0], [1, 1], [2, 0]]);
});

it('calculates total rounds from its size', function (int $size, int $rounds) {
    $bracket = Bracket::factory()->create(['size' => $size]);

    expect($bracket->totalRounds())->toBe($rounds);
})->with([
    [4, 2],
    [8, 3],
    [16, 4],
    [32, 5],
    [64, 6],
]);

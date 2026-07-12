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

it('names rounds by distance from the final', function () {
    $bracket = Bracket::factory()->create(['size' => 32]);

    expect($bracket->roundName(5))->toBe('Final')
        ->and($bracket->roundName(4))->toBe('Semifinals')
        ->and($bracket->roundName(3))->toBe('Quarterfinals')
        ->and($bracket->roundName(2))->toBe('Round of 16')
        ->and($bracket->roundName(1))->toBe('Round of 32');
});

it('has no champion until the bracket completes', function () {
    $bracket = Bracket::factory()->active()->create();

    expect($bracket->champion())->toBeNull();
});

it('crowns the final round winner as champion', function () {
    $bracket = Bracket::factory()->completed()->create(['size' => 4]);
    $winner = Contestant::factory()->for($bracket)->create();

    Matchup::factory()->for($bracket)->create(['round' => 2, 'position' => 0, 'winner_id' => $winner->id]);

    expect($bracket->champion()?->is($winner))->toBeTrue();
});

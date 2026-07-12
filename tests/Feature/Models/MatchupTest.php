<?php

use App\Models\Bracket;
use App\Models\Contestant;
use App\Models\Matchup;
use Illuminate\Database\QueryException;

it('is open while inside its voting window without a winner', function () {
    $matchup = Matchup::factory()->open()->create();

    expect($matchup->isOpen())->toBeTrue();
});

it('is not open before its voting window starts', function () {
    $matchup = Matchup::factory()->create([
        'opens_at' => now()->addHour(),
        'closes_at' => now()->addDay(),
    ]);

    expect($matchup->isOpen())->toBeFalse();
});

it('is not open after its voting window ends', function () {
    $matchup = Matchup::factory()->closed()->create();

    expect($matchup->isOpen())->toBeFalse();
});

it('is not open when it has no voting window yet', function () {
    $matchup = Matchup::factory()->create();

    expect($matchup->isOpen())->toBeFalse();
});

it('is not open once a winner has been decided', function () {
    $bracket = Bracket::factory()->create();
    $contestant = Contestant::factory()->for($bracket)->create();

    $matchup = Matchup::factory()->open()->for($bracket)->create([
        'contestant_one_id' => $contestant->id,
        'winner_id' => $contestant->id,
    ]);

    expect($matchup->isOpen())->toBeFalse();
});

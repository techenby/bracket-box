<?php

use App\Actions\DistributeSeeds;
use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\Contestant;

function rankedDraftBracket(int $size, ?int $contestants = null): Bracket
{
    $bracket = Bracket::factory()->create(['size' => $size]);

    Contestant::factory()
        ->count($contestants ?? $size)
        ->for($bracket)
        ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
        ->create();

    return $bracket;
}

it('reseeds contestants into tournament order', function (int $size, array $expectedOrder) {
    $bracket = rankedDraftBracket($size);
    $originalRanks = $bracket->contestants->pluck('seed', 'id');

    app(DistributeSeeds::class)->handle($bracket);

    $newOrder = $bracket->contestants()
        ->pluck('id')
        ->map(fn (int $id) => $originalRanks[$id])
        ->all();

    expect($newOrder)->toBe($expectedOrder);
})->with([
    [4, [1, 4, 2, 3]],
    [8, [1, 8, 4, 5, 2, 7, 3, 6]],
    [16, [1, 16, 8, 9, 4, 13, 5, 12, 2, 15, 7, 10, 3, 14, 6, 11]],
]);

it('pairs every rank with its mirror in a 64 contestant bracket', function () {
    $bracket = rankedDraftBracket(64);
    $originalRanks = $bracket->contestants->pluck('seed', 'id');

    app(DistributeSeeds::class)->handle($bracket);

    $pairSums = $bracket->contestants()
        ->pluck('id')
        ->map(fn (int $id) => $originalRanks[$id])
        ->chunk(2)
        ->map(fn ($pair) => $pair->sum());

    expect($pairSums)->each->toBe(65);
});

it('rejects brackets that are not drafts', function () {
    $bracket = Bracket::factory()->active()->create();

    app(DistributeSeeds::class)->handle($bracket);
})->throws(InvalidArgumentException::class, 'Only draft brackets can be reseeded.');

it('rejects brackets without a full field of contestants', function () {
    $bracket = rankedDraftBracket(size: 8, contestants: 5);

    app(DistributeSeeds::class)->handle($bracket);
})->throws(InvalidArgumentException::class, 'needs exactly 8 contestants');

it('leaves seeds untouched when the reseed is rejected', function () {
    $bracket = rankedDraftBracket(size: 8, contestants: 5);

    try {
        app(DistributeSeeds::class)->handle($bracket);
    } catch (InvalidArgumentException) {
        //
    }

    expect($bracket->contestants()->pluck('seed')->all())->toBe([1, 2, 3, 4, 5])
        ->and($bracket->refresh()->status)->toBe(BracketStatus::Draft);
});

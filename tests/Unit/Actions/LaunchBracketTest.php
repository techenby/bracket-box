<?php

use App\Actions\LaunchBracket;
use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\Contestant;

function draftBracketWithContestants(int $size, ?int $contestants = null): Bracket
{
    $bracket = Bracket::factory()->create(['size' => $size]);

    Contestant::factory()
        ->count($contestants ?? $size)
        ->for($bracket)
        ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
        ->create();

    return $bracket;
}

it('activates the bracket and opens round one', function () {
    $this->freezeSecond();

    $bracket = draftBracketWithContestants(4);

    app(LaunchBracket::class)->handle($bracket);

    expect($bracket->refresh())
        ->status->toBe(BracketStatus::Active)
        ->current_round->toBe(1);

    $roundOne = $bracket->matchups()->where('round', 1)->get();

    expect($roundOne)->each(
        fn ($matchup) => $matchup
            ->opens_at->toEqual(now())
            ->closes_at->toEqual(now()->addHours($bracket->round_duration_hours))
    );
});

it('generates the full matchup tree for every size', function (int $size, array $matchupsPerRound) {
    $bracket = draftBracketWithContestants($size);

    app(LaunchBracket::class)->handle($bracket);

    $counts = $bracket->matchups()
        ->get()
        ->groupBy('round')
        ->map->count()
        ->all();

    expect($counts)->toBe($matchupsPerRound);
})->with([
    [4, [1 => 2, 2 => 1]],
    [8, [1 => 4, 2 => 2, 3 => 1]],
    [16, [1 => 8, 2 => 4, 3 => 2, 4 => 1]],
]);

it('pairs round one contestants by seed order', function () {
    $bracket = draftBracketWithContestants(4);
    $seeds = $bracket->contestants->keyBy('seed');

    app(LaunchBracket::class)->handle($bracket);

    $roundOne = $bracket->matchups()->where('round', 1)->orderBy('position')->get();

    expect($roundOne[0]->contestant_one_id)->toBe($seeds[1]->id)
        ->and($roundOne[0]->contestant_two_id)->toBe($seeds[2]->id)
        ->and($roundOne[1]->contestant_one_id)->toBe($seeds[3]->id)
        ->and($roundOne[1]->contestant_two_id)->toBe($seeds[4]->id);
});

it('leaves later rounds without contestants or voting windows', function () {
    $bracket = draftBracketWithContestants(8);

    app(LaunchBracket::class)->handle($bracket);

    $laterRounds = $bracket->matchups()->where('round', '>', 1)->get();

    expect($laterRounds)->toHaveCount(3)->each(
        fn ($matchup) => $matchup
            ->contestant_one_id->toBeNull()
            ->contestant_two_id->toBeNull()
            ->opens_at->toBeNull()
            ->closes_at->toBeNull()
    );
});

it('rejects brackets that are not drafts', function () {
    $bracket = Bracket::factory()->active()->create();

    app(LaunchBracket::class)->handle($bracket);
})->throws(InvalidArgumentException::class, 'Only draft brackets can be launched.');

it('rejects brackets without a full field of contestants', function () {
    $bracket = draftBracketWithContestants(size: 8, contestants: 5);

    app(LaunchBracket::class)->handle($bracket);
})->throws(InvalidArgumentException::class, 'needs exactly 8 contestants');

it('creates no matchups when the launch is rejected', function () {
    $bracket = draftBracketWithContestants(size: 8, contestants: 5);

    app(LaunchBracket::class)->handle($bracket);

    expect($bracket->matchups()->count())->toBe(0)
        ->and($bracket->refresh()->status)->toBe(BracketStatus::Draft);
})->throws(InvalidArgumentException::class);

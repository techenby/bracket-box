<?php

use App\Actions\CloseRound;
use App\Actions\LaunchBracket;
use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\Contestant;
use App\Models\Matchup;
use App\Models\Vote;

function launchedBracket(int $size = 4): Bracket
{
    $bracket = Bracket::factory()->create(['size' => $size, 'round_duration_hours' => 24]);

    Contestant::factory()
        ->count($size)
        ->for($bracket)
        ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
        ->create();

    return app(LaunchBracket::class)->handle($bracket);
}

function voteFor(Matchup $matchup, ?int $contestantId, int $times = 1): void
{
    Vote::factory()->count($times)->create([
        'matchup_id' => $matchup->id,
        'contestant_id' => $contestantId,
    ]);
}

it('records the vote winner for each matchup in the round', function () {
    $bracket = launchedBracket(4);
    [$first, $second] = $bracket->matchups()->where('round', 1)->orderBy('position')->get();

    voteFor($first, $first->contestant_one_id, times: 3);
    voteFor($first, $first->contestant_two_id, times: 1);
    voteFor($second, $second->contestant_two_id, times: 2);

    app(CloseRound::class)->handle($bracket);

    expect($first->refresh())
        ->winner_id->toBe($first->contestant_one_id)
        ->decided_by_coin_flip->toBeFalse()
        ->and($second->refresh())
        ->winner_id->toBe($second->contestant_two_id)
        ->decided_by_coin_flip->toBeFalse();
});

it('advances winners into the correct next-round slots', function () {
    $bracket = launchedBracket(8);
    $roundOne = $bracket->matchups()->where('round', 1)->orderBy('position')->get();

    foreach ($roundOne as $matchup) {
        voteFor($matchup, $matchup->contestant_one_id);
    }

    app(CloseRound::class)->handle($bracket);

    $roundTwo = $bracket->matchups()->where('round', 2)->orderBy('position')->get();

    expect($roundTwo[0]->contestant_one_id)->toBe($roundOne[0]->contestant_one_id)
        ->and($roundTwo[0]->contestant_two_id)->toBe($roundOne[1]->contestant_one_id)
        ->and($roundTwo[1]->contestant_one_id)->toBe($roundOne[2]->contestant_one_id)
        ->and($roundTwo[1]->contestant_two_id)->toBe($roundOne[3]->contestant_one_id);
});

it('opens the next round with a fresh voting window', function () {
    $bracket = launchedBracket(4);

    $this->travel(25)->hours();
    $this->freezeSecond();

    app(CloseRound::class)->handle($bracket);

    expect($bracket->refresh()->current_round)->toBe(2);

    $final = $bracket->matchups()->where('round', 2)->sole();

    expect($final->opens_at)->toEqual(now())
        ->and($final->closes_at)->toEqual(now()->addHours(24));
});

it('flips a coin when a matchup is tied', function () {
    $bracket = launchedBracket(4);
    $matchup = $bracket->matchups()->where('round', 1)->orderBy('position')->first();

    voteFor($matchup, $matchup->contestant_one_id, times: 2);
    voteFor($matchup, $matchup->contestant_two_id, times: 2);

    app(CloseRound::class)->handle($bracket);

    expect($matchup->refresh())
        ->winner_id->toBeIn([$matchup->contestant_one_id, $matchup->contestant_two_id])
        ->decided_by_coin_flip->toBeTrue();
});

it('completes the bracket after closing the final round', function () {
    $bracket = launchedBracket(4);

    app(CloseRound::class)->handle($bracket);
    app(CloseRound::class)->handle($bracket);

    expect($bracket->refresh())
        ->status->toBe(BracketStatus::Completed)
        ->current_round->toBeNull()
        ->completed_at->not->toBeNull();

    $final = $bracket->matchups()->where('round', 2)->sole();

    expect($final->winner_id)->not->toBeNull();
});

it('rejects brackets that are not active', function () {
    $bracket = Bracket::factory()->create();

    app(CloseRound::class)->handle($bracket);
})->throws(InvalidArgumentException::class, 'Only active brackets');

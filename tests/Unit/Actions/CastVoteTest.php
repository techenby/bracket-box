<?php

use App\Actions\CastVote;
use App\Models\Bracket;
use App\Models\Contestant;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;

function openMatchupWithContestants(): Matchup
{
    $bracket = Bracket::factory()->active()->create();
    [$one, $two] = Contestant::factory()->count(2)->for($bracket)->create();

    return Matchup::factory()->open()->for($bracket)->create([
        'contestant_one_id' => $one->id,
        'contestant_two_id' => $two->id,
    ]);
}

it('records a guest vote with a hashed token', function () {
    $matchup = openMatchupWithContestants();

    $vote = app(CastVote::class)->handle($matchup, $matchup->contestantOne, guestToken: 'guest-token');

    expect($vote)
        ->matchup_id->toBe($matchup->id)
        ->contestant_id->toBe($matchup->contestant_one_id)
        ->user_id->toBeNull()
        ->voter_hash->not->toContain('guest-token');
});

it('records an authenticated user vote', function () {
    $matchup = openMatchupWithContestants();
    $user = User::factory()->create();

    $vote = app(CastVote::class)->handle($matchup, $matchup->contestantOne, user: $user);

    expect($vote->user_id)->toBe($user->id);
});

it('updates the existing vote when a voter changes their pick', function () {
    $matchup = openMatchupWithContestants();

    app(CastVote::class)->handle($matchup, $matchup->contestantOne, guestToken: 'guest-token');
    app(CastVote::class)->handle($matchup, $matchup->contestantTwo, guestToken: 'guest-token');

    expect(Vote::count())->toBe(1)
        ->and(Vote::sole()->contestant_id)->toBe($matchup->contestant_two_id);
});

it('counts distinct guests separately', function () {
    $matchup = openMatchupWithContestants();

    app(CastVote::class)->handle($matchup, $matchup->contestantOne, guestToken: 'first-guest');
    app(CastVote::class)->handle($matchup, $matchup->contestantOne, guestToken: 'second-guest');

    expect(Vote::count())->toBe(2);
});

it('never collides a user id with a guest token of the same value', function () {
    $matchup = openMatchupWithContestants();
    $user = User::factory()->create();

    app(CastVote::class)->handle($matchup, $matchup->contestantOne, user: $user);
    app(CastVote::class)->handle($matchup, $matchup->contestantOne, guestToken: (string) $user->id);

    expect(Vote::count())->toBe(2);
});

it('rejects a vote without any voter identity', function () {
    $matchup = openMatchupWithContestants();

    app(CastVote::class)->handle($matchup, $matchup->contestantOne);
})->throws(InvalidArgumentException::class, 'voter identity');

it('rejects a vote on a closed matchup', function () {
    $matchup = openMatchupWithContestants();
    $matchup->update(['opens_at' => now()->subDays(2), 'closes_at' => now()->subDay()]);

    app(CastVote::class)->handle($matchup, $matchup->contestantOne, guestToken: 'guest-token');
})->throws(InvalidArgumentException::class, 'not open');

it('rejects a vote on a matchup without a voting window', function () {
    $matchup = openMatchupWithContestants();
    $matchup->update(['opens_at' => null, 'closes_at' => null]);

    app(CastVote::class)->handle($matchup, $matchup->contestantOne, guestToken: 'guest-token');
})->throws(InvalidArgumentException::class, 'not open');

it('rejects a contestant that is not part of the matchup', function () {
    $matchup = openMatchupWithContestants();
    $outsider = Contestant::factory()->create();

    app(CastVote::class)->handle($matchup, $outsider, guestToken: 'guest-token');
})->throws(InvalidArgumentException::class, 'not part of this matchup');

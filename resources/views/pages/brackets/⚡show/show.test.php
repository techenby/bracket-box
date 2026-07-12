<?php

use App\Actions\CloseRound;
use App\Actions\LaunchBracket;
use App\Http\Middleware\EnsureVoterToken;
use App\Models\Bracket;
use App\Models\Contestant;
use App\Models\User;
use App\Models\Vote;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function publicBracket(int $size = 4): Bracket
{
    $bracket = Bracket::factory()->create(['size' => $size]);

    Contestant::factory()
        ->count($size)
        ->for($bracket)
        ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
        ->create();

    return app(LaunchBracket::class)->handle($bracket);
}

/** @return array{bracket: Bracket, voterToken: string} */
function asGuestVoter(Bracket $bracket, string $token = 'guest-token'): array
{
    return ['bracket' => $bracket, 'voterToken' => $token];
}

it('renders for guests and issues a voter token', function () {
    $bracket = publicBracket();

    get(route('brackets.show', $bracket))
        ->assertOk()
        ->assertSee($bracket->name)
        ->assertSee(__('Live tournament'))
        ->assertSee(__('Choose a favorite in each matchup.'))
        ->assertSee(__('Tournament map'))
        ->assertCookie(EnsureVoterToken::COOKIE);
});

it('hides drafts from guests and other users', function () {
    $draft = Bracket::factory()->create();

    get(route('brackets.show', $draft))->assertNotFound();

    actingAs(User::factory()->create())
        ->get(route('brackets.show', $draft))
        ->assertNotFound();
});

it('shows the owner a draft preview', function () {
    $draft = Bracket::factory()->create();
    $contestant = Contestant::factory()->for($draft)->create();

    actingAs($draft->user)
        ->get(route('brackets.show', $draft))
        ->assertOk()
        ->assertSee(__('Draft preview'))
        ->assertSee($contestant->name);
});

it('lets a guest vote', function () {
    $bracket = publicBracket();
    $matchup = $bracket->matchups()->where('round', 1)->orderBy('position')->first();

    Livewire::test('pages::brackets.show', asGuestVoter($bracket))
        ->call('vote', $matchup->id, $matchup->contestant_one_id);

    expect(Vote::sole())
        ->matchup_id->toBe($matchup->id)
        ->contestant_id->toBe($matchup->contestant_one_id)
        ->user_id->toBeNull();
});

it('lets an authenticated user vote', function () {
    $bracket = publicBracket();
    $matchup = $bracket->matchups()->where('round', 1)->orderBy('position')->first();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::brackets.show', ['bracket' => $bracket])
        ->call('vote', $matchup->id, $matchup->contestant_one_id);

    expect(Vote::sole()->user_id)->toBe($user->id);
});

it('lets a voter change their pick while the matchup is open', function () {
    $bracket = publicBracket();
    $matchup = $bracket->matchups()->where('round', 1)->orderBy('position')->first();

    Livewire::test('pages::brackets.show', asGuestVoter($bracket))
        ->call('vote', $matchup->id, $matchup->contestant_one_id)
        ->call('vote', $matchup->id, $matchup->contestant_two_id);

    expect(Vote::sole()->contestant_id)->toBe($matchup->contestant_two_id);
});

it('hides tallies until the voter has voted', function () {
    $bracket = publicBracket();
    $matchup = $bracket->matchups()->where('round', 1)->orderBy('position')->first();

    Livewire::test('pages::brackets.show', asGuestVoter($bracket))
        ->assertDontSee(__('Your pick'))
        ->call('vote', $matchup->id, $matchup->contestant_one_id)
        ->assertSee(__('Your pick'));
});

it('rejects votes without a voter identity', function () {
    $bracket = publicBracket();
    $matchup = $bracket->matchups()->where('round', 1)->orderBy('position')->first();

    Livewire::test('pages::brackets.show', ['bracket' => $bracket])
        ->call('vote', $matchup->id, $matchup->contestant_one_id)
        ->assertDispatched('toast-show');

    expect(Vote::count())->toBe(0);
});

it('rejects voting on a closed matchup with a toast', function () {
    $bracket = publicBracket();
    $bracket->matchups()->where('round', 1)->update([
        'opens_at' => now()->subDays(2),
        'closes_at' => now()->subDay(),
    ]);
    $matchup = $bracket->matchups()->where('round', 1)->orderBy('position')->first();

    Livewire::test('pages::brackets.show', asGuestVoter($bracket))
        ->call('vote', $matchup->id, $matchup->contestant_one_id)
        ->assertDispatched('toast-show');

    expect(Vote::count())->toBe(0);
});

it('shows the champion once the bracket completes', function () {
    $bracket = publicBracket();
    app(CloseRound::class)->handle($bracket);
    app(CloseRound::class)->handle($bracket);

    get(route('brackets.show', $bracket))
        ->assertOk()
        ->assertSee(__('Champion'))
        ->assertSee($bracket->champion()->name);
});

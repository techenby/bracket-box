<?php

use App\Actions\LaunchBracket;
use App\Models\Bracket;
use App\Models\Contestant;
use App\Models\Matchup;
use App\Models\Vote;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders for guests', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee(__('Pick a side.'))
        ->assertSee(__('Crown a champion.'));
});

it('lists active public brackets with links to their pages', function () {
    $bracket = Bracket::factory()->active()->create();

    Livewire::test('pages::gallery')
        ->assertSee($bracket->name)
        ->assertSee(route('brackets.show', $bracket))
        ->assertSee(__('Tournament board'))
        ->assertSee(__('Voting open'));
});

it('lists completed brackets', function () {
    $bracket = Bracket::factory()->completed()->create();

    Livewire::test('pages::gallery')
        ->assertSee($bracket->name)
        ->assertSee(__('Finished'));
});

it('hides unlisted and draft brackets', function () {
    $unlisted = Bracket::factory()->active()->unlisted()->create();
    $draft = Bracket::factory()->create();

    Livewire::test('pages::gallery')
        ->assertDontSee($unlisted->name)
        ->assertDontSee($draft->name);
});

it('shows a voting open eyebrow with hours remaining in brackets', function () {
    $this->freezeSecond();

    $bracket = Bracket::factory()->create(['size' => 4, 'round_duration_hours' => 12]);
    Contestant::factory()->count(4)->for($bracket)->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])->create();
    app(LaunchBracket::class)->handle($bracket);

    Livewire::test('pages::gallery')
        ->assertSee(__('Voting open'))
        ->assertSee('[ 12 hours left ]');
});

it('shows the total number of votes across all matchups', function () {
    $bracket = Bracket::factory()->active()->create(['size' => 4]);
    $matchups = Matchup::factory()->count(2)->for($bracket)->sequence(['position' => 0], ['position' => 1])->create(['round' => 1]);
    Vote::factory()->count(3)->for($matchups->first())->create();
    Vote::factory()->count(2)->for($matchups->last())->create();

    Livewire::test('pages::gallery')
        ->assertSee('5 votes');
});

it('shows days remaining for longer rounds', function () {
    $this->freezeSecond();

    $bracket = Bracket::factory()->create(['size' => 4, 'round_duration_hours' => 48]);
    Contestant::factory()->count(4)->for($bracket)->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])->create();
    app(LaunchBracket::class)->handle($bracket);

    Livewire::test('pages::gallery')
        ->assertSee('2 days left');
});

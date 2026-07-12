<?php

use App\Models\Bracket;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders for guests', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee(__('Pick a side.'))
        ->assertSee(__('Crown a champion.'))
        ->assertDontSee('data-uidotsh-pick', escape: false)
        ->assertDontSee('https://ui.sh/ui-picker.js')
        ->assertSee('href="https://techenby.com"', escape: false)
        ->assertSee('href="https://laravel.com"', escape: false)
        ->assertSee('href="https://cloud.laravel.com"', escape: false);
});

it('lists active public brackets with links to their pages', function () {
    $bracket = Bracket::factory()->active()->create();

    Livewire::test('pages::gallery')
        ->assertSee($bracket->name)
        ->assertSee(route('brackets.show', $bracket))
        ->assertSee(__('Tournament board'))
        ->assertSee(__('Voting open'))
        ->assertDontSee('data-uidotsh-option', escape: false);
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

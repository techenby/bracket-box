<?php

use App\Models\Bracket;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to the login page', function () {
    get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('allows authenticated users to visit the dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

it('lists only the user\'s own brackets', function () {
    $user = User::factory()->create();
    $mine = Bracket::factory()->for($user)->create(['name' => 'My Bracket']);
    $theirs = Bracket::factory()->create(['name' => 'Someone Elses Bracket']);

    Livewire::actingAs($user)
        ->test('pages::dashboard')
        ->assertSee($mine->name)
        ->assertDontSee($theirs->name);
});

it('links drafts to the edit page', function () {
    $bracket = Bracket::factory()->create();

    Livewire::actingAs($bracket->user)
        ->test('pages::dashboard')
        ->assertSee(route('brackets.edit', $bracket));
});

it('disables editing for active brackets and explains why', function () {
    $bracket = Bracket::factory()->active()->create();

    Livewire::actingAs($bracket->user)
        ->test('pages::dashboard')
        ->assertSee(__('Voting is underway — brackets cannot be edited after launch.'))
        ->assertDontSee(route('brackets.edit', $bracket));
});

it('disables editing for completed brackets and explains why', function () {
    $bracket = Bracket::factory()->completed()->create();

    Livewire::actingAs($bracket->user)
        ->test('pages::dashboard')
        ->assertSee(__('This bracket has finished and can no longer be edited.'))
        ->assertDontSee(route('brackets.edit', $bracket));
});

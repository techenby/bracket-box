<?php

use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to the login page', function () {
    get(route('brackets.create'))
        ->assertRedirect(route('login'));
});

it('renders for authenticated users', function () {
    actingAs(User::factory()->create())
        ->get(route('brackets.create'))
        ->assertOk();
});

it('creates a draft bracket and redirects to the edit page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::brackets.create')
        ->set('form.name', 'Best Soda')
        ->set('form.description', 'The fizzy showdown.')
        ->set('form.size', 8)
        ->set('form.roundDurationHours', 12)
        ->set('form.isUnlisted', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('toast-show');

    expect(Bracket::sole())
        ->user_id->toBe($user->id)
        ->name->toBe('Best Soda')
        ->status->toBe(BracketStatus::Draft)
        ->size->toBe(8)
        ->round_duration_hours->toBe(12)
        ->is_unlisted->toBeTrue()
        ->slug->toBe('best-soda');
});

it('increments the slug when the name is already taken', function () {
    Bracket::factory()->create(['slug' => 'best-soda']);

    Livewire::actingAs(User::factory()->create())
        ->test('pages::brackets.create')
        ->set('form.name', 'Best Soda')
        ->call('save')
        ->assertHasNoErrors();

    expect(Bracket::latest('id')->first()->slug)->toBe('best-soda-2');
});

it('validates the details', function (string $field, mixed $value) {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::brackets.create')
        ->set('form.name', 'Valid Name')
        ->set($field, $value)
        ->call('save')
        ->assertHasErrors($field);

    expect(Bracket::count())->toBe(0);
})->with([
    'missing name' => ['form.name', ''],
    'unsupported size' => ['form.size', 5],
    'unsupported duration' => ['form.roundDurationHours', 3],
]);

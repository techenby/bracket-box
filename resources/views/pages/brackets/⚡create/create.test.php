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
        ->set('name', 'Best Soda')
        ->set('description', 'The fizzy showdown.')
        ->set('size', 8)
        ->set('roundDurationHours', 12)
        ->set('isUnlisted', true)
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
        ->slug->toStartWith('best-soda-');
});

it('validates the details', function (string $field, mixed $value) {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::brackets.create')
        ->set('name', 'Valid Name')
        ->set($field, $value)
        ->call('save')
        ->assertHasErrors($field);

    expect(Bracket::count())->toBe(0);
})->with([
    'missing name' => ['name', ''],
    'unsupported size' => ['size', 5],
    'unsupported duration' => ['roundDurationHours', 3],
]);

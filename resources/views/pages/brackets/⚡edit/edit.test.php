<?php

use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\Contestant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function draftBracket(int $size = 4, int $contestants = 0): Bracket
{
    $bracket = Bracket::factory()->create(['size' => $size]);

    if ($contestants > 0) {
        Contestant::factory()
            ->count($contestants)
            ->for($bracket)
            ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
            ->create();
    }

    return $bracket;
}

it('renders for the bracket owner', function () {
    $bracket = draftBracket();

    actingAs($bracket->user)
        ->get(route('brackets.edit', $bracket))
        ->assertOk();
});

it('forbids users who do not own the bracket', function () {
    $bracket = draftBracket();

    actingAs(User::factory()->create())
        ->get(route('brackets.edit', $bracket))
        ->assertForbidden();
});

it('redirects to the dashboard when the bracket is no longer a draft', function () {
    $bracket = Bracket::factory()->active()->create();

    actingAs($bracket->user)
        ->get(route('brackets.edit', $bracket))
        ->assertRedirect(route('dashboard'));
});

it('updates the bracket details', function () {
    $bracket = draftBracket();

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->assertSet('form.name', $bracket->name)
        ->assertSet('form.size', $bracket->size)
        ->assertSet('form.roundDurationHours', $bracket->round_duration_hours)
        ->assertSet('form.isUnlisted', $bracket->is_unlisted)
        ->set('form.name', 'Renamed Bracket')
        ->set('form.size', 16)
        ->set('form.roundDurationHours', 48)
        ->set('form.isUnlisted', true)
        ->call('saveDetails')
        ->assertHasNoErrors();

    expect($bracket->refresh())
        ->name->toBe('Renamed Bracket')
        ->size->toBe(16)
        ->round_duration_hours->toBe(48)
        ->is_unlisted->toBeTrue();
});

it('rejects shrinking the size below the current contestant count', function () {
    $bracket = draftBracket(size: 8, contestants: 5);

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->set('form.size', 4)
        ->call('saveDetails')
        ->assertHasErrors('form.size');

    expect($bracket->refresh()->size)->toBe(8);
});

it('adds contestants with sequential seeds', function () {
    $bracket = draftBracket(size: 4, contestants: 2);

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->set('contestantForm.name', 'Topo Chico')
        ->call('addContestant')
        ->assertHasNoErrors()
        ->assertDispatched('toast-show');

    $contestant = $bracket->contestants()->where('name', 'Topo Chico')->sole();

    expect($contestant->seed)->toBe(3)
        ->and($contestant->image_path)->toBeNull();
});

it('stores an uploaded contestant image', function () {
    Storage::fake('public');

    $bracket = draftBracket();

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->set('contestantForm.name', 'Topo Chico')
        ->set('contestantForm.image', UploadedFile::fake()->image('topo.png'))
        ->call('addContestant')
        ->assertHasNoErrors();

    $contestant = $bracket->contestants()->sole();

    expect($contestant->image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($contestant->image_path);
});

it('does not add contestants beyond the bracket size', function () {
    $bracket = draftBracket(size: 4, contestants: 4);

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->set('contestantForm.name', 'One Too Many')
        ->call('addContestant');

    expect($bracket->contestants()->count())->toBe(4);
});

it('removes a contestant, deletes its image, and resequences seeds', function () {
    Storage::fake('public');

    $bracket = draftBracket(size: 4, contestants: 3);
    $second = $bracket->contestants()->where('seed', 2)->sole();
    $second->update(['image_path' => UploadedFile::fake()->image('gone.png')->store('contestants', 'public')]);

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->call('removeContestant', $second->id)
        ->assertDispatched('toast-show');

    expect($bracket->contestants()->pluck('seed')->all())->toBe([1, 2]);

    Storage::disk('public')->assertMissing($second->image_path);
});

it('reorders contestants and resequences seeds', function () {
    $bracket = Bracket::factory()->create(['size' => 4]);

    [$c, $md, $dp, $dc] = Contestant::factory()
        ->count(4)
        ->for($bracket)
        ->sequence(
            ['name' => 'Coke', 'seed' => 1],
            ['name' => 'Mountain Dew', 'seed' => 2],
            ['name' => 'Diet Pepsi', 'seed' => 3],
            ['name' => 'Diet Coke', 'seed' => 4],
        )
        ->create();

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->assertSeeInOrder([1, 'Coke', 2, 'Mountain Dew', 3, 'Diet Pepsi', 4, 'Diet Coke'])
        ->call('reorder', $dc->id, 0)
        ->assertSeeInOrder([1, 'Diet Coke', 2, 'Coke', 3, 'Mountain Dew', 4, 'Diet Pepsi'])
        ->assertDispatched('toast-show');

    expect($dc->refresh()->seed)->toBe(1)
        ->and($bracket->contestants()->pluck('seed')->all())->toBe([1, 2, 3, 4]);
});

it('sorts contestants by seed so the top seeds meet last', function () {
    $bracket = Bracket::factory()->create(['size' => 4]);

    Contestant::factory()
        ->count(4)
        ->for($bracket)
        ->sequence(
            ['name' => 'Coke', 'seed' => 1],
            ['name' => 'Mountain Dew', 'seed' => 2],
            ['name' => 'Diet Pepsi', 'seed' => 3],
            ['name' => 'Diet Coke', 'seed' => 4],
        )
        ->create();

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->call('sortBySeed')
        ->assertSeeInOrder([1, 'Coke', 2, 'Diet Coke', 3, 'Mountain Dew', 4, 'Diet Pepsi'])
        ->assertDispatched('toast-show');

    expect($bracket->contestants()->pluck('name')->all())
        ->toBe(['Coke', 'Diet Coke', 'Mountain Dew', 'Diet Pepsi']);
});

it('refuses to sort by seed before the bracket is full', function () {
    $bracket = draftBracket(size: 4, contestants: 2);

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->call('sortBySeed')
        ->assertDispatched('toast-show');

    expect($bracket->contestants()->pluck('seed')->all())->toBe([1, 2]);
});

it('launches a full bracket and redirects to the dashboard', function () {
    $bracket = draftBracket(size: 4, contestants: 4);

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->call('launch')
        ->assertDispatched('toast-show')
        ->assertRedirect(route('dashboard'));

    expect($bracket->refresh())
        ->status->toBe(BracketStatus::Active)
        ->current_round->toBe(1)
        ->and($bracket->matchups()->count())->toBe(3);
});

it('refuses to launch before the bracket is full', function () {
    $bracket = draftBracket(size: 4, contestants: 2);

    Livewire::actingAs($bracket->user)
        ->test('pages::brackets.edit', ['bracket' => $bracket])
        ->call('launch');

    expect($bracket->refresh()->status)->toBe(BracketStatus::Draft)
        ->and($bracket->matchups()->count())->toBe(0);
});

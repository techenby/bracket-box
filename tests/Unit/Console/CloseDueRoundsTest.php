<?php

use App\Actions\LaunchBracket;
use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\Contestant;
use Illuminate\Console\Scheduling\Schedule;

function dueBracket(): Bracket
{
    $bracket = Bracket::factory()->create(['size' => 4, 'round_duration_hours' => 24]);

    Contestant::factory()
        ->count(4)
        ->for($bracket)
        ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
        ->create();

    return app(LaunchBracket::class)->handle($bracket);
}

it('closes rounds whose voting window has passed', function () {
    $bracket = dueBracket();

    $this->travel(25)->hours();

    $this->artisan('brackets:close-due-rounds')->assertSuccessful();

    expect($bracket->refresh()->current_round)->toBe(2)
        ->and($bracket->matchups()->where('round', 1)->whereNull('winner_id')->count())->toBe(0);
});

it('leaves brackets alone while their round is still open', function () {
    $bracket = dueBracket();

    $this->artisan('brackets:close-due-rounds')->assertSuccessful();

    expect($bracket->refresh()->current_round)->toBe(1)
        ->and($bracket->matchups()->whereNotNull('winner_id')->count())->toBe(0);
});

it('ignores draft and completed brackets', function () {
    $draft = Bracket::factory()->create();
    $completed = Bracket::factory()->completed()->create();

    $this->travel(25)->hours();

    $this->artisan('brackets:close-due-rounds')->assertSuccessful();

    expect($draft->refresh()->status)->toBe(BracketStatus::Draft)
        ->and($completed->refresh()->status)->toBe(BracketStatus::Completed);
});

it('runs on the scheduler every minute', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command ?? '', 'brackets:close-due-rounds'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('* * * * *');
});

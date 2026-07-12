<?php

use App\Actions\LaunchBracket;
use App\Models\Bracket;
use App\Models\Contestant;

it('loads the gallery and bracket page without javascript errors', function () {
    $bracket = Bracket::factory()->create(['size' => 4, 'name' => 'Smoke Test Bracket']);

    Contestant::factory()
        ->count(4)
        ->for($bracket)
        ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
        ->create();

    app(LaunchBracket::class)->handle($bracket);

    visit(route('home'))
        ->assertSee('Smoke Test Bracket')
        ->assertNoJavascriptErrors();

    visit(route('brackets.show', $bracket))
        ->assertSee('Smoke Test Bracket')
        ->assertNoJavascriptErrors();
});

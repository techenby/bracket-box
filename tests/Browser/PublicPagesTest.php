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

it('keeps the tournament map inside the viewport for large brackets', function () {
    $bracket = Bracket::factory()->create(['size' => 64, 'name' => 'Mega Bracket']);

    Contestant::factory()
        ->count(64)
        ->for($bracket)
        ->sequence(fn ($sequence) => ['seed' => $sequence->index + 1])
        ->create();

    app(LaunchBracket::class)->handle($bracket);

    visit(route('brackets.show', $bracket))
        ->assertSee('Mega Bracket')
        ->assertNoJavascriptErrors()
        ->assertScript(
            "(() => { const map = document.querySelector('[data-tournament-map]'); return map.clientHeight <= window.innerHeight && map.scrollHeight > map.clientHeight; })()",
            true,
        )
        ->assertScript(
            "(() => { const map = document.querySelector('[data-tournament-map]'); return document.documentElement.scrollWidth <= window.innerWidth && map.scrollWidth > map.clientWidth; })()",
            true,
        );
});

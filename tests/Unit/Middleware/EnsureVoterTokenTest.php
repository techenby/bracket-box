<?php

use App\Http\Middleware\EnsureVoterToken;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
use function Pest\Laravel\withCookie;

beforeEach(function () {
    Route::middleware(['web', EnsureVoterToken::class])
        ->get('/voter-token-test', fn () => [
            'token' => request()->cookie(EnsureVoterToken::COOKIE),
        ]);
});

it('issues a voter token cookie to new visitors', function () {
    get('/voter-token-test')
        ->assertCookie(EnsureVoterToken::COOKIE);
});

it('makes the fresh token readable within the issuing request', function () {
    $response = get('/voter-token-test');

    expect($response->json('token'))->toBeString()->toHaveLength(40);
});

it('does not reissue a token when the visitor already has one', function () {
    $response = withCookie(EnsureVoterToken::COOKIE, 'existing-token')
        ->get('/voter-token-test')
        ->assertCookieMissing(EnsureVoterToken::COOKIE);

    expect($response->json('token'))->toBe('existing-token');
});

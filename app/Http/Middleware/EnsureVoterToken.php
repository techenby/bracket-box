<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureVoterToken
{
    public const COOKIE = 'voter_token';

    public const LIFETIME_MINUTES = 60 * 24 * 365 * 5; // 5 years

    /** @param  Closure(Request): (Response)  $next */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->cookies->has(self::COOKIE)) {
            $token = Str::random(40);

            $request->cookies->set(self::COOKIE, $token);

            Cookie::queue(cookie(self::COOKIE, $token, self::LIFETIME_MINUTES));
        }

        return $next($request);
    }
}

<?php

namespace App\Actions;

use App\Models\Contestant;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;

class CastVote
{
    public const MAX_VOTES_PER_MINUTE = 30;

    public function handle(Matchup $matchup, Contestant $contestant, ?User $user = null, ?string $guestToken = null): Vote
    {
        throw_if($user === null && $guestToken === null, new InvalidArgumentException('A voter identity is required to vote.'));

        throw_unless($matchup->isOpen(), new InvalidArgumentException('This matchup is not open for voting.'));

        throw_unless(
            in_array($contestant->id, [$matchup->contestant_one_id, $matchup->contestant_two_id], true),
            new InvalidArgumentException('The contestant is not part of this matchup.'),
        );

        $voterHash = $this->hashFor($user, $guestToken);

        throw_if(
            RateLimiter::tooManyAttempts('vote:'.$voterHash, self::MAX_VOTES_PER_MINUTE),
            new InvalidArgumentException('Too many votes in the last minute — slow down and try again shortly.'),
        );

        RateLimiter::hit('vote:'.$voterHash);

        return Vote::updateOrCreate(
            attributes: [
                'matchup_id' => $matchup->id,
                'voter_hash' => $voterHash,
            ],
            values: ['contestant_id' => $contestant->id, 'user_id' => $user?->id],
        );
    }

    public function hashFor(?User $user = null, ?string $token = null): string
    {
        $data = $user ? 'user:'.$user->id : 'guest:'.$token;

        return hash_hmac('sha256', $data, config('app.key'));
    }
}

<?php

namespace App\Actions;

use App\Models\Contestant;
use App\Models\Matchup;
use App\Models\User;
use App\Models\Vote;
use InvalidArgumentException;

class CastVote
{
    public function handle(Matchup $matchup, Contestant $contestant, ?User $user = null, ?string $guestToken = null): Vote
    {
        throw_if($user === null && $guestToken === null, new InvalidArgumentException('A voter identity is required to vote.'));

        throw_unless($matchup->isOpen(), new InvalidArgumentException('This matchup is not open for voting.'));

        throw_unless(
            in_array($contestant->id, [$matchup->contestant_one_id, $matchup->contestant_two_id], true),
            new InvalidArgumentException('The contestant is not part of this matchup.'),
        );

        return Vote::updateOrCreate(
            attributes: [
                'matchup_id' => $matchup->id,
                'voter_hash' => $this->hashFor($user, $guestToken),
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

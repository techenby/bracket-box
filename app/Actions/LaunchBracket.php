<?php

namespace App\Actions;

use App\Enums\BracketStatus;
use App\Models\Bracket;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LaunchBracket
{
    /**
     * Generate the full matchup tree for the bracket and open round one for voting.
     */
    public function handle(Bracket $bracket): Bracket
    {
        throw_if($bracket->status !== BracketStatus::Draft, new InvalidArgumentException('Only draft brackets can be launched.'));

        $contestants = $bracket->contestants()->get()->values();

        throw_if($contestants->count() !== $bracket->size, new InvalidArgumentException("The bracket needs exactly {$bracket->size} contestants to launch."));

        return DB::transaction(function () use ($bracket, $contestants) {
            foreach (range(1, $bracket->totalRounds()) as $round) {
                $matchupsInRound = intdiv($bracket->size, 2 ** $round);

                foreach (range(0, $matchupsInRound - 1) as $position) {
                    $bracket->matchups()->create([
                        'round' => $round,
                        'position' => $position,
                        'contestant_one_id' => $round === 1 ? $contestants[$position * 2]->id : null,
                        'contestant_two_id' => $round === 1 ? $contestants[$position * 2 + 1]->id : null,
                        'opens_at' => $round === 1 ? now() : null,
                        'closes_at' => $round === 1 ? now()->addHours($bracket->round_duration_hours) : null,
                    ]);
                }
            }

            $bracket->update([
                'status' => BracketStatus::Active,
                'current_round' => 1,
            ]);

            return $bracket;
        });
    }
}

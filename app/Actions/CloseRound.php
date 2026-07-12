<?php

namespace App\Actions;

use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\Matchup;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CloseRound
{
    /**
     * Tally the bracket's current round, then open the next round or crown a champion.
     */
    public function handle(Bracket $bracket): Bracket
    {
        throw_if(
            $bracket->status !== BracketStatus::Active || $bracket->current_round === null,
            new InvalidArgumentException('Only active brackets have a round to close.'),
        );

        return DB::transaction(function () use ($bracket) {
            $round = $bracket->current_round;

            $closed = $bracket->matchups()
                ->where('round', $round)
                ->get()
                ->each(fn (Matchup $matchup) => $this->decideWinner($matchup));

            if ($round === $bracket->totalRounds()) {
                $bracket->update([
                    'status' => BracketStatus::Completed,
                    'current_round' => null,
                    'completed_at' => now(),
                ]);

                return $bracket;
            }

            foreach ($closed as $matchup) {
                $bracket->matchups()
                    ->where('round', $round + 1)
                    ->where('position', intdiv($matchup->position, 2))
                    ->update([
                        $matchup->position % 2 === 0 ? 'contestant_one_id' : 'contestant_two_id' => $matchup->winner_id,
                    ]);
            }

            $bracket->matchups()->where('round', $round + 1)->update([
                'opens_at' => now(),
                'closes_at' => now()->addHours($bracket->round_duration_hours),
            ]);

            $bracket->update(['current_round' => $round + 1]);

            return $bracket;
        });
    }

    private function decideWinner(Matchup $matchup): void
    {
        $tallies = $matchup->votes()
            ->selectRaw('contestant_id, count(*) as total')
            ->groupBy('contestant_id')
            ->pluck('total', 'contestant_id');

        $votesForOne = (int) ($tallies[$matchup->contestant_one_id] ?? 0);
        $votesForTwo = (int) ($tallies[$matchup->contestant_two_id] ?? 0);

        if ($votesForOne === $votesForTwo) {
            $matchup->update([
                'winner_id' => random_int(0, 1) === 0 ? $matchup->contestant_one_id : $matchup->contestant_two_id,
                'decided_by_coin_flip' => true,
            ]);

            return;
        }

        $matchup->update([
            'winner_id' => $votesForOne > $votesForTwo ? $matchup->contestant_one_id : $matchup->contestant_two_id,
        ]);
    }
}

<?php

namespace App\Actions;

use App\Enums\BracketStatus;
use App\Models\Bracket;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DistributeSeeds
{
    /**
     * Reseed the bracket's contestants into standard tournament order, so the
     * top seed opens against the bottom seed and the top two seeds can only
     * meet in the final.
     */
    public function handle(Bracket $bracket): Bracket
    {
        throw_if($bracket->status !== BracketStatus::Draft, new InvalidArgumentException('Only draft brackets can be reseeded.'));

        $contestants = $bracket->contestants()->get()->values();

        throw_if($contestants->count() !== $bracket->size, new InvalidArgumentException("The bracket needs exactly {$bracket->size} contestants to distribute seeds."));

        return DB::transaction(function () use ($bracket, $contestants) {
            foreach ($this->tournamentOrder($bracket->size) as $position => $rank) {
                $contestants[$rank - 1]->update(['seed' => $position + 1]);
            }

            return $bracket;
        });
    }

    /**
     * Build the classic bracket order by mirroring the field round by round,
     * e.g. [1, 8, 4, 5, 2, 7, 3, 6] for eight contestants — adjacent pairs
     * become the first-round matchups.
     *
     * @return list<int>
     */
    private function tournamentOrder(int $size): array
    {
        $order = [1];

        while (count($order) < $size) {
            $mirror = count($order) * 2 + 1;

            $order = collect($order)
                ->flatMap(fn (int $rank) => [$rank, $mirror - $rank])
                ->all();
        }

        return $order;
    }
}

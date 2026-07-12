<?php

namespace App\Console\Commands;

use App\Actions\CloseRound;
use App\Enums\BracketStatus;
use App\Models\Bracket;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('brackets:close-due-rounds')]
#[Description('Close voting rounds whose window has passed and open the next round')]
class CloseDueRounds extends Command
{
    public function handle(CloseRound $closeRound): int
    {
        $due = Bracket::query()
            ->where('status', BracketStatus::Active)
            ->whereHas('matchups', fn (Builder $query) => $query
                ->whereColumn('matchups.round', 'brackets.current_round')
                ->where('closes_at', '<=', now()))
            ->get();

        $due->each(fn (Bracket $bracket) => $closeRound->handle($bracket));

        $this->info("Closed due rounds for {$due->count()} bracket(s).");

        return self::SUCCESS;
    }
}

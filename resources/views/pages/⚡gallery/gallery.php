<?php

use App\Enums\BracketStatus;
use App\Models\Bracket;
use App\Models\Matchup;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Brackets')] class extends Component
{
    /** @return Collection<int, Bracket> */
    #[Computed]
    public function brackets(): Collection
    {
        return Bracket::query()
            ->where('is_unlisted', false)
            ->whereIn('status', [BracketStatus::Active, BracketStatus::Completed])
            ->withCount('contestants')
            ->addSelect([
                'current_round_closes_at' => Matchup::select('closes_at')
                    ->whereColumn('matchups.bracket_id', 'brackets.id')
                    ->whereColumn('matchups.round', 'brackets.current_round')
                    ->orderBy('position')
                    ->limit(1),
            ])
            ->withCasts(['current_round_closes_at' => 'datetime'])
            ->latest()
            ->get();
    }

    public function timeRemainingLabel(Bracket $bracket): ?string
    {
        if ($bracket->current_round_closes_at === null) {
            return null;
        }

        $seconds = max(0, $bracket->current_round_closes_at->getTimestamp() - now()->getTimestamp());

        if ($seconds === 0) {
            return __('closing now');
        }

        $days = intdiv($seconds, 86400);

        if ($days >= 1) {
            return trans_choice(':count day left|:count days left', $days, ['count' => $days]);
        }

        $hours = intdiv($seconds, 3600);

        return $hours >= 1
            ? trans_choice(':count hour left|:count hours left', $hours, ['count' => $hours])
            : __('less than an hour left');
    }
};

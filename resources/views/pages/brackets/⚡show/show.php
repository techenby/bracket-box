<?php

use App\Actions\CastVote;
use App\Enums\BracketStatus;
use App\Http\Middleware\EnsureVoterToken;
use App\Models\Bracket;
use App\Models\Vote;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    public Bracket $bracket;

    #[Locked]
    public ?string $voterToken = null;

    public function mount(?string $voterToken = null): void
    {
        abort_if(
            $this->bracket->status === BracketStatus::Draft && $this->bracket->user_id !== Auth::id(),
            404,
        );

        $cookie = request()->cookie(EnsureVoterToken::COOKIE);

        $this->voterToken = $voterToken ?? (is_string($cookie) ? $cookie : null);
    }

    /** @return Collection<int, Collection<int, App\Models\Matchup>> */
    #[Computed]
    public function rounds(): Collection
    {
        return $this->bracket->matchups()
            ->with(['contestantOne', 'contestantTwo', 'winner'])
            ->withCount([
                'votes as votes_for_one_count' => fn ($query) => $query->whereColumn('contestant_id', 'matchups.contestant_one_id'),
                'votes as votes_for_two_count' => fn ($query) => $query->whereColumn('contestant_id', 'matchups.contestant_two_id'),
            ])
            ->get()
            ->groupBy('round');
    }

    /** @return array<int, int> map of matchup id to the contestant id this voter picked */
    #[Computed]
    public function myVotes(): array
    {
        $voterHash = $this->voterHash();

        if ($voterHash === null) {
            return [];
        }

        return Vote::query()
            ->whereIn('matchup_id', $this->bracket->matchups()->pluck('id'))
            ->where('voter_hash', $voterHash)
            ->pluck('contestant_id', 'matchup_id')
            ->all();
    }

    public function vote(int $matchupId, int $contestantId): void
    {
        $matchup = $this->bracket->matchups()->findOrFail($matchupId);
        $contestant = $this->bracket->contestants()->findOrFail($contestantId);

        try {
            app(CastVote::class)->handle($matchup, $contestant, Auth::user(), $this->voterToken);
        } catch (InvalidArgumentException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());

            return;
        }

        unset($this->rounds, $this->myVotes);
    }

    private function voterHash(): ?string
    {
        if (Auth::guest() && $this->voterToken === null) {
            return null;
        }

        return app(CastVote::class)->hashFor(Auth::user(), $this->voterToken);
    }
};

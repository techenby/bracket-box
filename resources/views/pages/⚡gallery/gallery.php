<?php

use App\Enums\BracketStatus;
use App\Models\Bracket;
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
            ->latest()
            ->get();
    }
};

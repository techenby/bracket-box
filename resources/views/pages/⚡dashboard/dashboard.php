<?php

use App\Models\Bracket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component
{
    /** @return Collection<int, Bracket> */
    #[Computed]
    public function brackets(): Collection
    {
        return Auth::user()->brackets()->withCount('contestants')->latest()->get();
    }
};

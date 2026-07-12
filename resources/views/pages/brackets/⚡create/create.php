<?php

use App\Enums\BracketStatus;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New bracket')] class extends Component
{
    public string $name = '';

    public string $description = '';

    public int $size = 8;

    public int $roundDurationHours = 24;

    public bool $isUnlisted = false;

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'size' => ['required', 'integer', 'in:4,8,16,32,64'],
            'roundDurationHours' => ['required', 'integer', 'in:6,12,24,48'],
            'isUnlisted' => ['boolean'],
        ]);

        $bracket = Auth::user()->brackets()->create([
            'name' => $this->name,
            'slug' => Str::slug($this->name).'-'.Str::lower(Str::random(6)),
            'description' => $this->description !== '' ? $this->description : null,
            'size' => $this->size,
            'status' => BracketStatus::Draft,
            'round_duration_hours' => $this->roundDurationHours,
            'is_unlisted' => $this->isUnlisted,
        ]);

        Flux::toast(variant: 'success', text: __('Bracket created — now add your contestants.'));

        $this->redirectRoute('brackets.edit', $bracket, navigate: true);
    }
};

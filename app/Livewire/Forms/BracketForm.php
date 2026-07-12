<?php

namespace App\Livewire\Forms;

use App\Enums\BracketStatus;
use App\Models\Bracket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Form;

class BracketForm extends Form
{
    public ?Bracket $bracket = null;

    public string $name = '';

    public string $description = '';

    public int $size = 8;

    public int $roundDurationHours = 24;

    public bool $isUnlisted = false;

    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'size' => ['required', 'integer', 'in:4,8,16,32,64', 'gte:'.$this->contestantCount()],
            'roundDurationHours' => ['required', 'integer', 'in:6,12,24,48'],
            'isUnlisted' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'size.gte' => __('The bracket already has :count contestants — remove some first.', ['count' => $this->contestantCount()]),
        ];
    }

    public function load(Bracket $bracket): void
    {
        $this->bracket = $bracket;
        $this->name = $bracket->name;
        $this->description = $bracket->description ?? '';
        $this->size = $bracket->size;
        $this->roundDurationHours = $bracket->round_duration_hours;
        $this->isUnlisted = $bracket->is_unlisted;
    }

    public function store(): Bracket
    {
        $this->validate();

        abort_unless(Auth::user() !== null, 401);

        return Auth::user()->brackets()->create([
            'name' => $this->name,
            'slug' => Str::slug($this->name).'-'.Str::lower(Str::random(6)),
            'description' => $this->description !== '' ? $this->description : null,
            'size' => $this->size,
            'status' => BracketStatus::Draft,
            'round_duration_hours' => $this->roundDurationHours,
            'is_unlisted' => $this->isUnlisted,
        ]);
    }

    public function update(): void
    {
        abort_if($this->bracket === null, 404);

        $this->validate();

        $this->bracket->update([
            'name' => $this->name,
            'description' => $this->description !== '' ? $this->description : null,
            'size' => $this->size,
            'round_duration_hours' => $this->roundDurationHours,
            'is_unlisted' => $this->isUnlisted,
        ]);
    }

    private function contestantCount(): int
    {
        return (int) $this->bracket?->contestants()->count();
    }
}

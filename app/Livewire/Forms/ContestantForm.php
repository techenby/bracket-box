<?php

namespace App\Livewire\Forms;

use App\Models\Bracket;
use App\Models\Contestant;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class ContestantForm extends Form
{
    public string $name = '';

    public ?TemporaryUploadedFile $image = null;

    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function store(Bracket $bracket): Contestant
    {
        $this->validate();

        $contestant = $bracket->contestants()->create([
            'name' => $this->name,
            'image_path' => $this->image?->storePublicly('contestants', config('filesystems.contestants_disk')),
            'seed' => ((int) $bracket->contestants()->max('seed')) + 1,
        ]);

        $this->reset();

        return $contestant;
    }
}

<?php

use App\Actions\LaunchBracket;
use App\Enums\BracketStatus;
use App\Livewire\Forms\BracketForm;
use App\Livewire\Forms\ContestantForm;
use App\Models\Bracket;
use App\Models\Contestant;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit bracket')] class extends Component
{
    use WithFileUploads;

    public Bracket $bracket;

    public BracketForm $form;

    public ContestantForm $contestantForm;

    public function mount(): void
    {
        $this->authorize('update', $this->bracket);

        if ($this->bracket->status !== BracketStatus::Draft) {
            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        $this->form->load($this->bracket);
    }

    /** @return Collection<int, Contestant> */
    #[Computed]
    public function contestants(): Collection
    {
        return $this->bracket->contestants()->get();
    }

    public function saveDetails(): void
    {
        $this->authorize('update', $this->bracket);

        $this->form->update();

        Flux::toast(variant: 'success', text: __('Bracket updated.'));
    }

    public function addContestant(): void
    {
        $this->authorize('update', $this->bracket);

        if ($this->contestants->count() >= $this->bracket->size) {
            Flux::toast(variant: 'danger', text: __('This bracket already has :size contestants.', ['size' => $this->bracket->size]));

            return;
        }

        $this->contestantForm->store($this->bracket);

        unset($this->contestants);

        Flux::toast(variant: 'success', text: __('Contestant added.'));
    }

    public function removeContestant(Contestant $contestant): void
    {
        $this->authorize('update', $this->bracket);

        abort_unless($contestant->bracket_id === $this->bracket->id, 404);

        if ($contestant->image_path) {
            Storage::disk(config('filesystems.contestants_disk'))->delete($contestant->image_path);
        }

        $contestant->delete();

        $this->resequenceSeeds($this->contestants);

        unset($this->contestants);

        Flux::toast(variant: 'success', text: __('Contestant removed.'));
    }

    public function reorder(int $item, int $position): void
    {
        $this->authorize('update', $this->bracket);

        $contestants = $this->bracket->contestants()->get();
        $moved = $contestants->firstWhere('id', $item);

        abort_unless($moved !== null, 404);

        $reordered = $contestants->where('id', '!=', $moved->id)->values();
        $reordered->splice($position, 0, [$moved]);

        $this->resequenceSeeds($reordered);

        unset($this->contestants);

        Flux::toast(variant: 'success', text: __('Contestant order saved.'));
    }

    public function launch(LaunchBracket $launchBracket): void
    {
        $this->authorize('launch', $this->bracket);

        if ($this->contestants->count() !== $this->bracket->size) {
            Flux::toast(variant: 'danger', text: __('Add exactly :size contestants before launching.', ['size' => $this->bracket->size]));

            return;
        }

        $launchBracket->handle($this->bracket);

        Flux::toast(variant: 'success', text: __('Bracket launched — round one is open for voting.'));

        $this->redirectRoute('dashboard', navigate: true);
    }

    /** @param Collection<int, Contestant> $contestants */
    private function resequenceSeeds(Collection $contestants): void
    {
        $contestants->each(
            fn (Contestant $contestant, int $index) => $contestant->update(['seed' => $index + 1]),
        );
    }
};

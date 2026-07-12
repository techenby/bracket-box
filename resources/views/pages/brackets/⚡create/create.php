<?php

use App\Livewire\Forms\BracketForm;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New bracket')] class extends Component
{
    public BracketForm $form;

    public function save(): void
    {
        $bracket = $this->form->store();

        Flux::toast(variant: 'success', text: __('Bracket created — now add your contestants.'));

        $this->redirectRoute('brackets.edit', $bracket, navigate: true);
    }
};

<flux:input wire:model="form.name" :label="__('Name')" />

<flux:textarea wire:model="form.description" :label="__('Description')" rows="3" />

<flux:select wire:model="form.size" :label="__('Number of contestants')">
    @foreach ([4, 8, 16, 32, 64] as $option)
        <flux:select.option value="{{ $option }}">{{ __(':count contestants', ['count' => $option]) }}</flux:select.option>
    @endforeach
</flux:select>

<flux:select wire:model="form.roundDurationHours" :label="__('Voting time per round')">
    <flux:select.option value="6">{{ __('6 hours') }}</flux:select.option>
    <flux:select.option value="12">{{ __('12 hours') }}</flux:select.option>
    <flux:select.option value="24">{{ __('24 hours') }}</flux:select.option>
    <flux:select.option value="48">{{ __('48 hours') }}</flux:select.option>
</flux:select>

<flux:field variant="inline">
    <flux:switch wire:model="form.isUnlisted" />
    <flux:label>{{ __('Unlisted — hide from the public gallery') }}</flux:label>
</flux:field>

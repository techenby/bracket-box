<div class="mx-auto max-w-xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('New bracket') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Set up your competition, then add contestants before launching.') }}</flux:text>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="name" :label="__('Name')" :placeholder="__('Best soda in the Laravel community')" />

        <flux:textarea wire:model="description" :label="__('Description')" rows="3" :placeholder="__('What is this bracket all about? (optional)')" />

        <flux:select wire:model="size" :label="__('Number of contestants')">
            @foreach ([4, 8, 16, 32, 64] as $option)
                <flux:select.option value="{{ $option }}">{{ __(':count contestants', ['count' => $option]) }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="roundDurationHours" :label="__('Voting time per round')">
            <flux:select.option value="6">{{ __('6 hours') }}</flux:select.option>
            <flux:select.option value="12">{{ __('12 hours') }}</flux:select.option>
            <flux:select.option value="24">{{ __('24 hours') }}</flux:select.option>
            <flux:select.option value="48">{{ __('48 hours') }}</flux:select.option>
        </flux:select>

        <flux:field variant="inline">
            <flux:switch wire:model="isUnlisted" />
            <flux:label>{{ __('Unlisted — hide from the public gallery') }}</flux:label>
        </flux:field>

        <div class="flex justify-end gap-2">
            <flux:button :href="route('dashboard')" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Create bracket') }}</flux:button>
        </div>
    </form>
</div>

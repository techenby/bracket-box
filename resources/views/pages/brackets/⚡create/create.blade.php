<div class="mx-auto max-w-xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('New bracket') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Set up your competition, then add contestants before launching.') }}</flux:text>
    </div>

    <form wire:submit="save" class="space-y-6">
        @include('pages::brackets.partials.form')

        <div class="flex justify-end gap-2">
            <flux:button :href="route('dashboard')" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Create bracket') }}</flux:button>
        </div>
    </form>
</div>

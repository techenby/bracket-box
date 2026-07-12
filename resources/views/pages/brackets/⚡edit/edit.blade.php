<div class="mx-auto max-w-2xl space-y-10">
    <div>
        <flux:heading size="xl">{{ $bracket->name }}</flux:heading>
        <flux:text class="mt-1">{{ __('Fill out the details and add contestants, then launch to open voting.') }}</flux:text>
    </div>

    <section class="space-y-6">
        <flux:heading size="lg">{{ __('Details') }}</flux:heading>

        <form wire:submit="saveDetails" class="space-y-6">
            @include('pages::brackets.partials.form')

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">{{ __('Save details') }}</flux:button>
            </div>
        </form>
    </section>

    <flux:separator />

    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Contestants') }}</flux:heading>
            <flux:badge size="sm" :color="$this->contestants->count() === $bracket->size ? 'green' : 'zinc'">
                {{ $this->contestants->count() }} / {{ $bracket->size }}
            </flux:badge>
        </div>

        @if ($this->contestants->isNotEmpty())
            <flux:text size="sm">{{ __('Drag to reorder — the order sets the first-round matchups: 1 plays 2, 3 plays 4, and so on.') }}</flux:text>

            <div wire:sort="reorder" class="space-y-2">
                @foreach ($this->contestants as $contestant)
                    <div
                        wire:sort:item="{{ $contestant->id }}"
                        wire:key="contestant-{{ $contestant->id }}"
                        class="flex cursor-grab items-center gap-3 rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900"
                    >
                        <span class="w-6 text-sm font-medium text-neutral-400">{{ $contestant->seed }}</span>

                        @if ($contestant->imageUrl())
                            <img src="{{ $contestant->imageUrl() }}" alt="{{ $contestant->name }}" class="size-10 rounded-lg object-cover">
                        @else
                            <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                                <flux:icon name="photo" variant="mini" class="text-neutral-400" />
                            </div>
                        @endif

                        <span class="flex-1 font-medium">{{ $contestant->name }}</span>

                        <flux:button
                            size="sm"
                            variant="subtle"
                            icon="trash"
                            wire:click="removeContestant({{ $contestant->id }})"
                            :aria-label="__('Remove :name', ['name' => $contestant->name])"
                        />
                    </div>
                @endforeach
            </div>
        @endif

        @if ($this->contestants->count() < $bracket->size)
            <form wire:submit="addContestant" class="flex items-start gap-2">
                <div class="flex-1">
                    <flux:input wire:model="contestantForm.name" :label="__('Add contestant')" :placeholder="__('Topo Chico')" />
                </div>

                <div class="flex-1">
                    <flux:input type="file" wire:model="contestantForm.image" :label="__('Image (optional)')" accept="image/*" />
                    <flux:text size="sm" wire:loading wire:target="contestantForm.image">{{ __('Uploading…') }}</flux:text>
                </div>

                <flux:button type="submit" variant="filled" class="mt-6" data-loading:disabled>{{ __('Add') }}</flux:button>
            </form>
        @endif
    </section>

    <flux:separator />

    <section class="space-y-4">
        <flux:heading size="lg">{{ __('Launch') }}</flux:heading>

        @if ($this->contestants->count() === $bracket->size)
            <flux:text>{{ __('Everything is ready. Launching locks in the matchups and opens round one for voting.') }}</flux:text>
        @else
            <flux:text>{{ __('Add :remaining more contestant(s) to launch this bracket.', ['remaining' => $bracket->size - $this->contestants->count()]) }}</flux:text>
        @endif

        <flux:modal.trigger name="launch-bracket">
            <flux:button variant="primary" icon="rocket-launch" :disabled="$this->contestants->count() !== $bracket->size">
                {{ __('Launch bracket') }}
            </flux:button>
        </flux:modal.trigger>

        @teleport('body')
        <flux:modal name="launch-bracket" class="min-w-88">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Launch this bracket?') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('The matchups will be locked in and round one opens for voting immediately. Contestants can no longer be changed after launch.') }}
                    </flux:text>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" wire:click="launch">{{ __('Launch') }}</flux:button>
                </div>
            </div>
        </flux:modal>
        @endteleport
    </section>
</div>

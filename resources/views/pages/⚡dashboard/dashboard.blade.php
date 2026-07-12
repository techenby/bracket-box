<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Your brackets') }}</flux:heading>

        <flux:button variant="primary" icon="plus" :href="route('brackets.create')" wire:navigate>
            {{ __('New bracket') }}
        </flux:button>
    </div>

    @if ($this->brackets->isEmpty())
        <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-neutral-200 py-16 dark:border-neutral-700">
            <flux:icon name="trophy" class="size-8 text-neutral-400" />
            <flux:heading>{{ __('No brackets yet') }}</flux:heading>
            <flux:text>{{ __('Create a bracket and let the community pick a champion.') }}</flux:text>
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Contestants') }}</flux:table.column>
                <flux:table.column>{{ __('Created') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->brackets as $bracket)
                    <flux:table.row wire:key="bracket-{{ $bracket->id }}">
                        <flux:table.cell variant="strong">{{ $bracket->name }}</flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="match ($bracket->status) {
                                App\Enums\BracketStatus::Draft => 'zinc',
                                App\Enums\BracketStatus::Active => 'green',
                                App\Enums\BracketStatus::Completed => 'blue',
                            }">
                                {{ ucfirst($bracket->status->value) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>{{ $bracket->contestants_count }} / {{ $bracket->size }}</flux:table.cell>

                        <flux:table.cell>{{ $bracket->created_at->diffForHumans() }}</flux:table.cell>

                        <flux:table.cell align="end">
                            @if ($bracket->status === App\Enums\BracketStatus::Draft)
                                <flux:button size="sm" :href="route('brackets.edit', $bracket)" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>
                            @else
                                <flux:tooltip :content="$bracket->status === App\Enums\BracketStatus::Active
                                    ? __('Voting is underway — brackets cannot be edited after launch.')
                                    : __('This bracket has finished and can no longer be edited.')">
                                    <div class="inline-flex">
                                        <flux:button size="sm" disabled>
                                            {{ __('Edit') }}
                                        </flux:button>
                                    </div>
                                </flux:tooltip>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

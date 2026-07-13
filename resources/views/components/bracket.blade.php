@props([
    'bracket',
    'iteration'
])

<li>
    <a
        href="{{ route('brackets.show', $bracket) }}"
        wire:navigate
        wire:key="bracket-board-{{ $bracket->id }}"
        class="group grid gap-4 p-5 outline-none hover:bg-yellow-50 focus-visible:bg-yellow-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-orange-600 dark:hover:bg-orange-400/5 dark:focus-visible:bg-orange-400/5 dark:focus-visible:outline-orange-400 sm:grid-cols-[4.5rem_minmax(0,1fr)_auto] sm:items-center sm:p-7"
    >
        <p class="font-pixel text-2xl text-neutral-300 tabular-nums group-hover:text-orange-700 group-focus-visible:text-orange-700 dark:text-neutral-700 dark:group-hover:text-orange-400 dark:group-focus-visible:text-orange-400 sm:text-3xl">
            {{ str_pad((string) $iteration, 2, '0', STR_PAD_LEFT) }}
        </p>

        <div class="min-w-0">
            <div class="flex min-w-0 flex-wrap items-baseline gap-x-3 gap-y-2">
                <h3 class="min-w-0 truncate font-editorial text-2xl tracking-tight text-neutral-900 dark:text-neutral-100 sm:text-3xl">
                    {{ $bracket->name }}
                </h3>

                @if ($this->timeRemainingLabel($bracket))
                    <p class="shrink-0 font-pixel text-[0.5625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                        [ {{ $this->timeRemainingLabel($bracket) }} ]
                    </p>
                @endif
            </div>

            <p class="font-code text-base/7 text-neutral-500 dark:text-neutral-400 sm:text-sm/6">
                @if ($bracket->completed_at)
                {{ __('Finished · :count contestants', ['count' => $bracket->contestants_count]) }}
                · {{ trans_choice(':count vote|:count votes', $bracket->votes_count, ['count' => number_format($bracket->votes_count)]) }}
                @else
                {{ __(':round · :count contestants', ['round' => $bracket->roundName($bracket->current_round), 'count' => $bracket->contestants_count]) }}
                · {{ trans_choice(':count vote|:count votes', $bracket->votes_count, ['count' => number_format($bracket->votes_count)]) }}
                @endif
            </p>
        </div>

        <div class="flex items-center justify-between gap-5 sm:justify-end">
            @if ($bracket->completed_at)
                <p class="flex min-w-0 items-center gap-2 border-2 border-neutral-900 bg-yellow-100 py-1.5 pr-3 pl-1.5 font-pixel text-[0.5625rem] tracking-wide text-neutral-900 uppercase dark:border-white/15 dark:bg-orange-400/10 dark:text-orange-300">
                    <flux:icon name="trophy" variant="micro" class="size-3.5 shrink-0 text-orange-700 dark:text-orange-400" />
                    <span class="truncate">{{ $bracket->champion_name }}</span>
                </p>
            @else
            <div class="flex gap-1" aria-label="{{ __('Round :current of :total', ['current' => $bracket->current_round, 'total' => $bracket->totalRounds()]) }}">
                @for ($round = 1; $round <= $bracket->totalRounds(); $round++)
                    <span aria-hidden="true" class="size-3 border border-neutral-900 dark:border-white/20 {{ $round <= $bracket->current_round ? 'bg-orange-600 dark:bg-orange-400' : 'bg-transparent' }}"></span>
                @endfor
            </div>
            @endif

            <div aria-hidden="true" class="grid size-10 shrink-0 place-items-center border-2 border-neutral-900 bg-yellow-100 font-code text-lg text-neutral-900 shadow-[3px_3px_0_0_#171717] transition-transform group-hover:-translate-y-0.5 group-focus-visible:-translate-y-0.5 dark:border-white/15 dark:bg-orange-400/10 dark:text-orange-300 dark:shadow-none">
                &rarr;
            </div>
        </div>
    </a>
</li>

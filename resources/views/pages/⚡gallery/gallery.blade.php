<div class="grid gap-8 sm:gap-10">
    <section class="relative border-2 border-neutral-900 bg-white p-6 shadow-[6px_6px_0_0_#171717] dark:border-white/15 dark:bg-neutral-900 dark:inset-ring dark:inset-ring-white/5 dark:shadow-none sm:p-10">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-60 [background-image:repeating-linear-gradient(0deg,transparent_0_3px,rgba(0,0,0,0.025)_3px_4px)] dark:[background-image:repeating-linear-gradient(0deg,transparent_0_3px,rgba(255,255,255,0.025)_3px_4px)]"></div>

        <div class="relative grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_auto]">
            <div class="grid gap-5">
                <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                    <span aria-hidden="true">&#9654;&nbsp;</span>{{ __('The people decide') }}
                </p>
                <h1 class="max-w-[20ch] font-editorial text-5xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-7xl lg:text-8xl">
                    {{ __('Pick a side.') }}
                    <em class="text-orange-700 dark:text-orange-400">{{ __('Crown a champion.') }}</em>
                </h1>
                <p class="max-w-[48ch] font-code text-base/7 text-pretty text-neutral-600 dark:text-neutral-400 sm:text-sm/6">
                    {{ __('Community brackets decided one matchup at a time. Choose your favorites and help send them to the top.') }}
                </p>
            </div>

            <div class="grid grid-cols-[auto_1fr] items-center gap-4 border-t-2 border-dashed border-neutral-300 pt-5 dark:border-white/10 lg:grid-cols-1 lg:justify-items-end lg:border-t-0 lg:border-l-2 lg:py-2 lg:pt-2 lg:pl-8">
                <p class="font-pixel text-4xl text-neutral-900 tabular-nums dark:text-neutral-100 sm:text-5xl">
                    {{ str_pad((string) $this->brackets->count(), 2, '0', STR_PAD_LEFT) }}
                </p>
                <p class="max-w-[16ch] font-code text-base/6 tracking-wide text-neutral-500 uppercase dark:text-neutral-400 sm:text-sm/5 lg:text-right">
                    {{ __('public brackets to explore') }}
                </p>
            </div>
        </div>
    </section>

    @if ($this->brackets->isEmpty())
        <section class="grid justify-items-center gap-4 border-2 border-dashed border-neutral-400 bg-stone-100 p-10 text-center dark:border-white/15 dark:bg-neutral-900 sm:p-16">
            <flux:icon name="trophy" class="size-6 text-orange-700 dark:text-orange-400" />
            <h2 class="font-editorial text-3xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-4xl">
                {{ __('No brackets are running right now') }}
            </h2>
            <p class="max-w-[48ch] font-code text-base/7 text-pretty text-neutral-600 dark:text-neutral-400 sm:text-sm/6">
                {{ __('Check back soon—or sign in and start your own.') }}
            </p>
        </section>
    @else
        <section class="border-2 border-neutral-900 bg-white shadow-[6px_6px_0_0_#171717] dark:border-white/15 dark:bg-neutral-900 dark:inset-ring dark:inset-ring-white/5 dark:shadow-none">
            <header class="grid gap-4 border-b-2 border-neutral-900 p-5 dark:border-white/15 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end sm:p-7">
                <div class="grid gap-2">
                    <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                        <span aria-hidden="true">&#9654;&nbsp;</span>{{ __('Tournament board') }}
                    </p>
                    <h2 class="max-w-[30ch] font-editorial text-3xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-4xl">
                        {{ __('Choose your next matchup') }}
                    </h2>
                </div>

                <p class="flex items-center gap-2 font-code text-base/6 text-neutral-500 uppercase dark:text-neutral-400 sm:text-sm/5">
                    <span aria-hidden="true" class="size-2 shrink-0 bg-orange-700 [animation:blink_1s_step-end_infinite] motion-reduce:animate-none dark:bg-orange-400"></span>
                    {{ __('Updated live') }}
                </p>
            </header>

            <ol role="list" class="divide-y-2 divide-dashed divide-neutral-300 dark:divide-white/10">
                @foreach ($this->brackets as $bracket)
                    <li>
                        <a
                            href="{{ route('brackets.show', $bracket) }}"
                            wire:navigate
                            wire:key="bracket-board-{{ $bracket->id }}"
                            class="group grid gap-4 p-5 outline-none hover:bg-yellow-50 focus-visible:bg-yellow-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-orange-600 dark:hover:bg-orange-400/5 dark:focus-visible:bg-orange-400/5 dark:focus-visible:outline-orange-400 sm:grid-cols-[4.5rem_minmax(0,1fr)_auto] sm:items-center sm:p-7"
                        >
                            <p class="font-pixel text-2xl text-neutral-300 tabular-nums group-hover:text-orange-700 group-focus-visible:text-orange-700 dark:text-neutral-700 dark:group-hover:text-orange-400 dark:group-focus-visible:text-orange-400 sm:text-3xl">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </p>

                            <div class="min-w-0">
                                <div class="flex min-w-0 flex-wrap items-baseline gap-x-3 gap-y-2">
                                    <h3 class="min-w-0 truncate font-editorial text-2xl tracking-tight text-neutral-900 dark:text-neutral-100 sm:text-3xl">
                                        {{ $bracket->name }}
                                    </h3>

                                    @if ($bracket->status === App\Enums\BracketStatus::Active)
                                        <p class="shrink-0 font-pixel text-[0.5625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                                            [ {{ __('Voting open') }} ]
                                        </p>
                                    @endif
                                </div>

                                <p class="font-code text-base/7 text-neutral-500 dark:text-neutral-400 sm:text-sm/6">
                                    @if ($bracket->status === App\Enums\BracketStatus::Active)
                                        {{ __(':round · :count contestants', ['round' => $bracket->roundName($bracket->current_round), 'count' => $bracket->contestants_count]) }}
                                    @else
                                        {{ __('Finished · :count contestants', ['count' => $bracket->contestants_count]) }}
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-5 sm:justify-end">
                                <div class="flex gap-1" aria-label="{{ __('Round :current of :total', ['current' => $bracket->current_round ?? $bracket->totalRounds(), 'total' => $bracket->totalRounds()]) }}">
                                    @for ($round = 1; $round <= $bracket->totalRounds(); $round++)
                                        <span aria-hidden="true" class="size-3 border border-neutral-900 dark:border-white/20 {{ $round <= ($bracket->current_round ?? $bracket->totalRounds()) ? 'bg-orange-600 dark:bg-orange-400' : 'bg-transparent' }}"></span>
                                    @endfor
                                </div>

                                <div aria-hidden="true" class="grid size-10 shrink-0 place-items-center border-2 border-neutral-900 bg-yellow-100 font-code text-lg text-neutral-900 shadow-[3px_3px_0_0_#171717] transition-transform group-hover:-translate-y-0.5 group-focus-visible:-translate-y-0.5 dark:border-white/15 dark:bg-orange-400/10 dark:text-orange-300 dark:shadow-none">
                                    &rarr;
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
</div>

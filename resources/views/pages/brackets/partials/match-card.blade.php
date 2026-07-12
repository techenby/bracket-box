@php
    $matchNumber = $matchNumber ?? null;
    $totalVotes = $matchup->votes_for_one_count + $matchup->votes_for_two_count;
    $revealTallies = $myVote !== null || $matchup->winner_id !== null;
    $sides = [
        ['contestant' => $matchup->contestantOne, 'votes' => $matchup->votes_for_one_count],
        ['contestant' => $matchup->contestantTwo, 'votes' => $matchup->votes_for_two_count],
    ];
@endphp

<article class="border-2 border-neutral-900 bg-white shadow-[4px_4px_0_0_#171717] dark:border-white/15 dark:bg-neutral-900 dark:inset-ring dark:inset-ring-white/5 dark:shadow-none">
    <header class="flex items-center justify-between gap-4 border-b-2 border-neutral-900 bg-stone-50 p-3 dark:border-white/15 dark:bg-neutral-950">
        <p class="font-pixel text-[0.5625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
            {{ __('Match :number', ['number' => str_pad((string) ($matchNumber ?? $matchup->position), 2, '0', STR_PAD_LEFT)]) }}
        </p>
        <p class="font-code text-sm/6 text-neutral-400 uppercase dark:text-neutral-500">
            {{ $revealTallies ? __('Results') : __('Choose one') }}
        </p>
    </header>

    <div class="divide-y-2 divide-dashed divide-neutral-300 dark:divide-white/10">
        @foreach ($sides as $side)
            @php
                $contestant = $side['contestant'];
                $percentage = $totalVotes > 0 ? (int) round($side['votes'] / $totalVotes * 100) : 0;
                $isMyVote = $contestant !== null && $myVote === $contestant->id;
                $isWinner = $matchup->winner_id !== null && $matchup->winner_id === $contestant?->id;
            @endphp

            <div wire:key="side-{{ $matchup->id }}-{{ $loop->index }}">
                @if ($revealTallies)
                    <div class="grid gap-3 p-4 {{ $isMyVote ? 'bg-yellow-50 dark:bg-orange-400/5' : '' }}">
                        <div class="flex min-w-0 items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                @if ($contestant?->imageUrl())
                                    <img src="{{ $contestant->imageUrl() }}" alt="" class="size-10 shrink-0 object-cover outline-1 -outline-offset-1 outline-black/10 dark:outline-white/10">
                                @endif

                                <div class="min-w-0">
                                    <p class="truncate font-editorial text-2xl tracking-tight {{ $isWinner ? 'text-orange-700 dark:text-orange-400' : 'text-neutral-900 dark:text-neutral-100' }}">
                                        {{ $contestant?->name ?? __('TBD') }}
                                    </p>

                                    @if ($isMyVote)
                                        <p class="font-pixel text-[0.5rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                                            [ {{ __('Your pick') }} ]
                                        </p>
                                    @elseif ($isWinner)
                                        <p class="font-pixel text-[0.5rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                                            [ {{ __('Winner') }} ]
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                @if ($isWinner)
                                    <flux:icon name="check-circle" variant="micro" class="size-4 shrink-0 fill-orange-700 dark:fill-orange-400" />
                                @endif
                                <p class="font-pixel text-[0.625rem] text-neutral-900 tabular-nums dark:text-neutral-100">{{ $percentage }}%</p>
                            </div>
                        </div>

                        <div
                            role="progressbar"
                            aria-label="{{ __('Votes for :contestant', ['contestant' => $contestant?->name ?? __('TBD')]) }}"
                            aria-valuenow="{{ $percentage }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            class="h-3 border-2 border-neutral-900 bg-stone-100 dark:border-white/15 dark:bg-neutral-950"
                        >
                            <div class="h-full w-(--vote-percentage) {{ $isWinner || $isMyVote ? 'bg-orange-600 dark:bg-orange-400' : 'bg-neutral-300 dark:bg-neutral-700' }}" style="--vote-percentage: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @else
                    <button
                        type="button"
                        wire:click="vote({{ $matchup->id }}, {{ $contestant?->id }})"
                        wire:loading.attr="disabled"
                        @disabled($contestant === null || ! $matchup->isOpen())
                        class="group relative flex min-h-18 w-full items-center gap-3 p-4 text-left outline-none hover:bg-yellow-50 focus-visible:bg-yellow-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-orange-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-orange-400/5 dark:focus-visible:bg-orange-400/5 dark:focus-visible:outline-orange-400"
                    >
                        <span class="pointer-fine:hidden absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2" aria-hidden="true"></span>

                        @if ($contestant?->imageUrl())
                            <img src="{{ $contestant->imageUrl() }}" alt="" class="size-12 shrink-0 object-cover outline-1 -outline-offset-1 outline-black/10 dark:outline-white/10">
                        @endif

                        <p class="min-w-0 flex-1 truncate font-editorial text-2xl tracking-tight text-neutral-900 dark:text-neutral-100">
                            {{ $contestant?->name ?? __('TBD') }}
                        </p>
                        <p aria-hidden="true" class="shrink-0 font-code text-lg text-orange-700 dark:text-orange-400">&rarr;</p>
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    @if ($matchup->decided_by_coin_flip || $revealTallies)
        <footer class="flex flex-wrap items-center justify-between gap-3 border-t-2 border-neutral-900 bg-stone-50 p-3 dark:border-white/15 dark:bg-neutral-950">
            @if ($matchup->decided_by_coin_flip)
                <p class="flex items-center gap-2 font-code text-base/7 text-neutral-500 dark:text-neutral-400 sm:text-sm/6">
                    <flux:icon name="sparkles" variant="micro" class="size-4 shrink-0 fill-orange-700 dark:fill-orange-400" />
                    {{ __('Tie broken by coin flip') }}
                </p>
            @endif

            @if ($revealTallies)
                <p class="font-code text-base/7 text-neutral-500 tabular-nums dark:text-neutral-400 sm:text-sm/6">
                    {{ trans_choice(':count vote|:count votes', $totalVotes, ['count' => $totalVotes]) }}
                </p>
            @endif
        </footer>
    @endif
</article>

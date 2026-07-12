@php
    $champion = $bracket->status === App\Enums\BracketStatus::Completed ? $bracket->champion() : null;
@endphp

<div class="grid gap-8 sm:gap-10">
    <div class="flex items-center justify-between gap-4">
        <p class="font-code text-base/7 text-neutral-500 dark:text-neutral-400 sm:text-sm/6">
            <a href="{{ route('home') }}" wire:navigate class="font-normal text-orange-700 underline decoration-orange-700/40 underline-offset-4 hover:decoration-orange-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-600 dark:text-orange-400 dark:decoration-orange-400/40 dark:hover:decoration-orange-400 dark:focus-visible:outline-orange-400">
                &larr; {{ __('Browse brackets') }}
            </a>
        </p>

        @if ($bracket->status !== App\Enums\BracketStatus::Draft)
            <button
                type="button"
                x-data="{ copied: false }"
                x-on:click="navigator.clipboard.writeText(@js(route('brackets.show', $bracket))).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                class="inline-flex shrink-0 items-center gap-2 border-2 border-neutral-900 bg-white px-3 py-2 font-pixel text-[0.5625rem] tracking-wide text-neutral-900 uppercase shadow-[3px_3px_0_0_#171717] outline-none hover:bg-yellow-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 dark:border-white/15 dark:bg-neutral-900 dark:text-neutral-100 dark:shadow-none dark:inset-ring dark:inset-ring-white/5 dark:hover:bg-orange-400/5 dark:focus-visible:outline-orange-400"
            >
                <flux:icon name="link" variant="micro" class="size-3.5 shrink-0 fill-orange-700 dark:fill-orange-400" />
                <span x-show="!copied">{{ __('Share') }}</span>
                <span x-show="copied" x-cloak class="text-orange-700 dark:text-orange-400">{{ __('Copied!') }}</span>
            </button>
        @endif
    </div>

    <section class="relative border-2 border-neutral-900 bg-white p-6 shadow-[6px_6px_0_0_#171717] dark:border-white/15 dark:bg-neutral-900 dark:inset-ring dark:inset-ring-white/5 dark:shadow-none sm:p-10">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-60 [background-image:repeating-linear-gradient(0deg,transparent_0_3px,rgba(0,0,0,0.025)_3px_4px)] dark:[background-image:repeating-linear-gradient(0deg,transparent_0_3px,rgba(255,255,255,0.025)_3px_4px)]"></div>

        <div class="relative grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_auto]">
            <div class="grid gap-5">
                <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                    <span aria-hidden="true">&#9654;&nbsp;</span>
                    @if ($bracket->status === App\Enums\BracketStatus::Active)
                        {{ __('Live tournament') }}
                    @elseif ($bracket->status === App\Enums\BracketStatus::Completed)
                        {{ __('Final results') }}
                    @else
                        {{ __('Bracket preview') }}
                    @endif
                </p>

                <h1 class="max-w-[20ch] font-editorial text-5xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-7xl lg:text-8xl">
                    {{ $bracket->name }}
                </h1>

                @if ($bracket->description)
                    <p class="max-w-[48ch] font-code text-base/7 text-pretty text-neutral-600 dark:text-neutral-400 sm:text-sm/6">
                        {{ $bracket->description }}
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-[auto_1fr] items-center gap-4 border-t-2 border-dashed border-neutral-300 pt-5 dark:border-white/10 lg:grid-cols-1 lg:justify-items-end lg:border-t-0 lg:border-l-2 lg:py-2 lg:pt-2 lg:pl-8">
                <p class="font-pixel text-4xl text-neutral-900 tabular-nums dark:text-neutral-100 sm:text-5xl">
                    @if ($bracket->status === App\Enums\BracketStatus::Active)
                        {{ str_pad((string) $bracket->current_round, 2, '0', STR_PAD_LEFT) }}
                    @elseif ($bracket->status === App\Enums\BracketStatus::Completed)
                        GG
                    @else
                        00
                    @endif
                </p>
                <p class="max-w-[18ch] font-code text-base/6 tracking-wide text-neutral-500 uppercase dark:text-neutral-400 sm:text-sm/5 lg:text-right">
                    @if ($bracket->status === App\Enums\BracketStatus::Active)
                        {{ $bracket->roundName($bracket->current_round) }} · {{ __('Voting open') }}
                    @elseif ($bracket->status === App\Enums\BracketStatus::Completed)
                        {{ __('Tournament complete') }}
                    @else
                        {{ __('Private draft') }}
                    @endif
                </p>
            </div>
        </div>
    </section>

    @if ($bracket->status === App\Enums\BracketStatus::Draft)
        <section class="border-2 border-neutral-900 bg-white shadow-[6px_6px_0_0_#171717] dark:border-white/15 dark:bg-neutral-900 dark:inset-ring dark:inset-ring-white/5 dark:shadow-none">
            <header class="grid gap-2 border-b-2 border-neutral-900 p-5 dark:border-white/15 sm:p-7">
                <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                    <span aria-hidden="true">&#9654;&nbsp;</span>{{ __('Draft preview') }}
                </p>
                <h2 class="max-w-[30ch] font-editorial text-3xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-4xl">
                    {{ __('Contestant roster') }}
                </h2>
                <p class="max-w-[48ch] font-code text-base/7 text-pretty text-neutral-600 dark:text-neutral-400 sm:text-sm/6">
                    {{ __('Only you can see this page until the bracket launches.') }}
                </p>
            </header>

            <ol role="list" class="divide-y-2 divide-dashed divide-neutral-300 dark:divide-white/10">
                @foreach ($bracket->contestants as $contestant)
                    <li wire:key="contestant-{{ $contestant->id }}" class="grid grid-cols-[3rem_minmax(0,1fr)] items-center gap-4 p-4 sm:p-5">
                        <p class="font-pixel text-base text-neutral-300 tabular-nums dark:text-neutral-700">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </p>
                        <p class="min-w-0 truncate font-editorial text-2xl tracking-tight text-neutral-900 dark:text-neutral-100">
                            {{ $contestant->name }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </section>
    @else
        @if ($champion)
            <section class="relative overflow-hidden border-2 border-neutral-900 bg-yellow-100 p-6 shadow-[6px_6px_0_0_#171717] dark:border-white/15 dark:bg-neutral-900 dark:inset-ring dark:inset-ring-white/5 dark:shadow-none sm:p-8">
                <div aria-hidden="true" class="absolute -top-5 -right-2 font-pixel text-8xl text-orange-700/10 dark:text-white/5 sm:text-9xl">1UP</div>

                <div class="relative grid justify-items-start gap-5 sm:grid-cols-[auto_minmax(0,1fr)] sm:items-center sm:gap-7">
                    @if ($champion->imageUrl())
                        <img src="{{ $champion->imageUrl() }}" alt="" class="size-24 object-cover outline-1 -outline-offset-1 outline-black/10 dark:outline-white/10 sm:size-32">
                    @else
                        <flux:icon name="trophy" class="size-6 text-orange-700 dark:text-orange-400" />
                    @endif

                    <div class="grid gap-2">
                        <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                            <span aria-hidden="true">&#9654;&nbsp;</span>{{ __('Champion') }}
                        </p>
                        <h2 class="max-w-[30ch] font-editorial text-4xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-6xl">
                            {{ $champion->name }}
                        </h2>
                    </div>
                </div>
            </section>
        @endif

        @if ($bracket->status === App\Enums\BracketStatus::Active)
            <section class="grid gap-5" wire:poll.15s.visible>
                <header class="flex flex-col gap-3 border-b-2 border-neutral-900 pb-4 dark:border-white/15 sm:flex-row sm:items-end sm:justify-between">
                    <div class="grid gap-2">
                        <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                            <span aria-hidden="true">&#9654;&nbsp;</span>{{ __('Vote now') }}
                        </p>
                        <h2 class="max-w-[30ch] font-editorial text-3xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-4xl">
                            {{ $bracket->roundName($bracket->current_round) }}
                        </h2>
                        <p class="font-code text-base/7 text-neutral-500 dark:text-neutral-400 sm:text-sm/6">
                            {{ __('Choose a favorite in each matchup.') }}
                        </p>
                    </div>

                    @if ($this->currentRoundClosesAt)
                        <div class="grid gap-1 sm:justify-items-end" wire:key="countdown-round-{{ $bracket->current_round }}">
                            <p class="font-pixel text-[0.5625rem] tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                                {{ __('Round closes in') }}
                            </p>
                            <p
                                x-data="{
                                    target: {{ $this->currentRoundClosesAt->timestamp }},
                                    remaining: 0,
                                    tick() { this.remaining = Math.max(0, this.target - Math.floor(Date.now() / 1000)) },
                                    get display() {
                                        const pad = (n) => String(n).padStart(2, '0')
                                        const days = Math.floor(this.remaining / 86400)
                                        const hours = Math.floor((this.remaining % 86400) / 3600)
                                        const minutes = Math.floor((this.remaining % 3600) / 60)
                                        const seconds = this.remaining % 60

                                        return (days > 0 ? days + 'd ' : '') + pad(hours) + ':' + pad(minutes) + ':' + pad(seconds)
                                    },
                                }"
                                x-init="tick(); setInterval(() => tick(), 1000)"
                                class="font-pixel text-2xl text-neutral-900 tabular-nums dark:text-neutral-100 sm:text-3xl"
                                aria-live="off"
                            >
                                <span x-text="display">--:--:--</span>
                            </p>
                        </div>
                    @endif
                </header>

                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ($this->rounds[$bracket->current_round] ?? [] as $matchup)
                        <div wire:key="matchup-{{ $matchup->id }}">
                            @include('pages::brackets.partials.match-card', [
                                'matchup' => $matchup,
                                'matchNumber' => $loop->iteration,
                                'myVote' => $this->myVotes[$matchup->id] ?? null,
                            ])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="min-w-0 border-2 border-neutral-900 bg-white shadow-[6px_6px_0_0_#171717] dark:border-white/15 dark:bg-neutral-900 dark:inset-ring dark:inset-ring-white/5 dark:shadow-none">
            <header class="grid gap-3 border-b-2 border-neutral-900 p-5 dark:border-white/15 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end sm:p-7">
                <div class="grid gap-2">
                    <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                        <span aria-hidden="true">&#9654;&nbsp;</span>{{ __('Tournament map') }}
                    </p>
                    <h2 class="max-w-[30ch] font-editorial text-3xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-4xl">
                        {{ __('The road to the final') }}
                    </h2>
                </div>
                <p class="font-code text-base/7 text-neutral-500 dark:text-neutral-400 sm:text-sm/6">
                    {{ __('Scroll to explore every round.') }}
                </p>
            </header>

            <div class="overflow-x-auto p-5 sm:p-7">
                <div class="flex min-w-max gap-6">
                    @foreach ($this->rounds as $round => $matchups)
                        <section class="flex w-60 flex-col gap-4" wire:key="round-{{ $round }}">
                            <div class="flex items-baseline justify-between gap-3 border-b-2 border-dashed border-neutral-300 pb-3 dark:border-white/10">
                                <h3 class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                                    {{ $bracket->roundName($round) }}
                                </h3>
                                <p class="font-code text-sm/6 text-neutral-400 tabular-nums dark:text-neutral-500">
                                    {{ str_pad((string) $round, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>

                            <div class="flex flex-1 flex-col justify-around gap-4">
                                @foreach ($matchups as $matchup)
                                    <div wire:key="tree-matchup-{{ $matchup->id }}" class="divide-y-2 divide-neutral-900 border-2 border-neutral-900 bg-stone-50 dark:divide-white/15 dark:border-white/15 dark:bg-neutral-950">
                                        @foreach ([$matchup->contestantOne, $matchup->contestantTwo] as $contestant)
                                            @php
                                                $isWinner = $matchup->winner_id !== null && $matchup->winner_id === $contestant?->id;
                                                $isEliminated = $matchup->winner_id !== null && ! $isWinner;
                                            @endphp

                                            <div class="flex min-w-0 items-center justify-between gap-2 p-3 {{ $isWinner ? 'bg-yellow-100 dark:bg-orange-400/10' : '' }} {{ $isEliminated ? 'text-neutral-400 dark:text-neutral-600' : 'text-neutral-900 dark:text-neutral-100' }}">
                                                <p class="min-w-0 truncate font-code text-sm/6 {{ $isWinner ? 'font-semibold' : 'font-normal' }}">
                                                    {{ $contestant?->name ?? __('TBD') }}
                                                </p>

                                                @if ($isWinner)
                                                    <flux:icon name="check" variant="micro" class="size-4 shrink-0 fill-orange-700 dark:fill-orange-400" />
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>

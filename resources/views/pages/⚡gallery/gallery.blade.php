<div class="grid gap-8 sm:gap-10">
    <x-card class="p-6 sm:p-10">
        <div class="relative grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_auto]">
            <div class="grid gap-5">
                <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                    <span aria-hidden="true">&#9654;&nbsp;</span>{{ __('The people decide') }}
                </p>
                <h1 class="max-w-[20ch] font-editorial text-5xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-7xl lg:text-8xl">
                    {{ __('Pick a side.') }}<br>
                    <em class="text-orange-700 dark:text-orange-400">{{ __('Crown a champion.') }}</em>
                </h1>
                <p class="max-w-[48ch] font-code text-base/7 text-pretty text-neutral-600 dark:text-neutral-400 sm:text-sm/6">
                    {{ __('Community brackets decided one matchup at a time. Choose your favorites and help send them to the top.') }}
                </p>
            </div>

            <dl class="grid grid-cols-2 gap-6 border-t-2 border-dashed border-neutral-300 pt-5 dark:border-white/10 lg:grid-cols-1 lg:gap-7 lg:border-t-0 lg:border-l-2 lg:py-2 lg:pl-8">
                <div class="grid gap-1 lg:justify-items-end">
                    <dd class="font-pixel text-4xl text-neutral-900 tabular-nums dark:text-neutral-100 sm:text-5xl">
                        {{ str_pad((string) $this->activeBrackets->count(), 2, '0', STR_PAD_LEFT) }}
                    </dd>
                    <dt class="max-w-[16ch] font-code text-base/6 tracking-wide text-neutral-500 uppercase dark:text-neutral-400 sm:text-sm/5 lg:text-right">
                        {{ __('live right now') }}
                    </dt>
                </div>
                <div class="grid gap-1 lg:justify-items-end">
                    <dd class="font-pixel text-4xl text-neutral-900 tabular-nums dark:text-neutral-100 sm:text-5xl">
                        {{ str_pad((string) $this->completedBrackets->count(), 2, '0', STR_PAD_LEFT) }}
                    </dd>
                    <dt class="max-w-[16ch] font-code text-base/6 tracking-wide text-neutral-500 uppercase dark:text-neutral-400 sm:text-sm/5 lg:text-right">
                        {{ __('champions crowned') }}
                    </dt>
                </div>
            </dl>
        </div>
    </x-card>

    @if ($this->activeBrackets->isEmpty())
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
        <x-bracket-list :brackets="$this->activeBrackets" eyebrow="Tournament board" heading="Choose your next matchup" />
    @endif

    @if ($this->completedBrackets->isNotEmpty())
        <x-bracket-list :brackets="$this->completedBrackets" eyebrow="Hall of champions" heading="Brackets that went the distance" />
    @endif
</div>

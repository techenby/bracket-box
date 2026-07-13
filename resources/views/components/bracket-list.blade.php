@props([
    'heading',
    'eyebrow',
    'subtitle',
    'brackets'
])

<x-card>
    <header class="grid gap-4 border-b-2 border-neutral-900 p-5 dark:border-white/15 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end sm:p-7">
        <div class="grid gap-2">
            <p class="font-pixel text-[0.625rem] tracking-wide text-orange-700 uppercase dark:text-orange-400">
                <span aria-hidden="true">&#9654;&nbsp;</span>{{ __($eyebrow) }}
            </p>
            <h2 class="max-w-[30ch] font-editorial text-3xl tracking-tight text-balance text-neutral-900 dark:text-neutral-100 sm:text-4xl">
                {{ __($heading) }}
            </h2>
        </div>

        @if ($brackets->first()->completed_at)
        <p class="flex items-center gap-2 font-code text-base/6 text-neutral-500 uppercase dark:text-neutral-400 sm:text-sm/5">
            <span aria-hidden="true" class="size-2 shrink-0 bg-neutral-400 dark:bg-neutral-600"></span>
            {{ __('Votes closed') }}
        </p>
        @else
        <p class="flex items-center gap-2 font-code text-base/6 text-neutral-500 uppercase dark:text-neutral-400 sm:text-sm/5">
            <span aria-hidden="true" class="size-2 shrink-0 bg-orange-700 animate-[blink_1s_step-end_infinite] motion-reduce:animate-none dark:bg-orange-400"></span>
            {{ __('Updated live') }}
        </p>
        @endif
    </header>

    <ol role="list" class="divide-y-2 divide-dashed divide-neutral-300 dark:divide-white/10">
        @foreach ($brackets as $bracket)
            <x-bracket :$bracket :iteration="$loop->iteration" />
        @endforeach
    </ol>
</x-card>

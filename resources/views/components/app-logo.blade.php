@props([
    'sidebar' => false,
    'retro' => false,
])

@if($retro)
    <a {{ $attributes->class('flex items-center gap-3 font-pixel text-[0.625rem] tracking-wide text-neutral-900 uppercase dark:text-neutral-100') }}>
        <span class="grid size-8 shrink-0 place-items-center border-2 border-neutral-900 bg-yellow-100 text-orange-700 shadow-[3px_3px_0_0_#171717] dark:border-white/15 dark:bg-orange-400/10 dark:text-orange-400 dark:shadow-none">
            <x-app-logo-icon class="size-4 fill-current" />
        </span>
        <span>{{ __('Bracket Box') }}</span>
    </a>
@elseif($sidebar)
    <flux:sidebar.brand name="Laravel Starter Kit" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Laravel Starter Kit" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark antialiased">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-dvh bg-stone-50 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100">
        <div aria-hidden="true" class="pointer-events-none fixed inset-0 [background-image:repeating-linear-gradient(0deg,transparent_0_3px,rgba(0,0,0,0.04)_3px_4px)] dark:[background-image:repeating-linear-gradient(0deg,transparent_0_3px,rgba(255,255,255,0.035)_3px_4px)]"></div>

        <div class="relative flex! min-h-dvh flex-col">
            <flux:header container class="relative z-10 border-b-2 border-neutral-900 bg-stone-50 [&>div]:box-border dark:border-white/15 dark:bg-neutral-950">
                <x-app-logo href="{{ route('home') }}" wire:navigate />

                <flux:spacer />

                <flux:navbar class="me-1.5">
                    @auth
                        <flux:navbar.item :href="route('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navbar.item>
                    @else
                        <flux:navbar.item :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:navbar.item>
                        <flux:navbar.item :href="route('register')" wire:navigate class="max-sm:hidden">{{ __('Register') }}</flux:navbar.item>
                    @endauth
                </flux:navbar>
            </flux:header>

            <flux:main class="relative isolate z-0 min-w-0 flex-1 p-0!">
                <div class="mx-auto box-border min-w-0 w-full max-w-7xl px-6 py-6 lg:px-8 lg:py-8">
                    {{ $slot }}
                </div>
            </flux:main>

            <footer class="relative z-10 border-t-2 border-neutral-900 bg-stone-50 dark:border-white/15 dark:bg-neutral-950">
                <div class="mx-auto box-border w-full max-w-7xl px-6 py-5 lg:px-8">
                    <p class="font-code text-base/7 text-pretty text-neutral-500 dark:text-neutral-400 sm:text-sm/6">
                        {{ __('Made by') }}
                        <a href="https://techenby.com" target="_blank" rel="noopener noreferrer" class="font-normal text-orange-700 underline decoration-orange-700/40 underline-offset-4 hover:decoration-orange-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-600 dark:text-orange-400 dark:decoration-orange-400/40 dark:hover:decoration-orange-400 dark:focus-visible:outline-orange-400">TechEnby</a>
                        {{ __('on') }}
                        <a href="https://laravel.com" target="_blank" rel="noopener noreferrer" class="font-normal text-orange-700 underline decoration-orange-700/40 underline-offset-4 hover:decoration-orange-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-600 dark:text-orange-400 dark:decoration-orange-400/40 dark:hover:decoration-orange-400 dark:focus-visible:outline-orange-400">Laravel</a>.
                        {{ __('Hosted on') }}
                        <a href="https://cloud.laravel.com" target="_blank" rel="noopener noreferrer" class="font-normal text-orange-700 underline decoration-orange-700/40 underline-offset-4 hover:decoration-orange-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-600 dark:text-orange-400 dark:decoration-orange-400/40 dark:hover:decoration-orange-400 dark:focus-visible:outline-orange-400">Laravel Cloud</a>.
                    </p>
                </div>
            </footer>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

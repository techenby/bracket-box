@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name')" :logo="asset('logo.png')" {{ $attributes->merge(['class' => 'font-code']) }} />
@else
    <flux:brand :name="config('app.name')" :logo="asset('logo.png')" {{ $attributes->merge(['class' => 'font-code']) }}/>
@endif

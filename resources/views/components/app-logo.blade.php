@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="SIE SMS A2P" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-xl bg-gradient-to-br from-royal-600 to-blue-500 text-white shadow-sm shadow-royal-600/25">
            <flux:icon name="chart-bar-square" class="size-4 text-white" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="SIE SMS A2P" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-xl bg-gradient-to-br from-royal-600 to-blue-500 text-white shadow-sm shadow-royal-600/25">
            <flux:icon name="chart-bar-square" class="size-4 text-white" />
        </x-slot>
    </flux:brand>
@endif

<x-layouts::app :title="__('Dashboard Analitik SMS A2P')">
    @php
        $user = auth()->user();
        $roleLabel = $user?->hasRole('admin-it')
            ? 'Admin IT'
            : ($user?->hasRole('eksekutif') ? 'Eksekutif' : 'Pengguna Umum');
        $roleColor = $user?->hasRole('admin-it')
            ? 'blue'
            : ($user?->hasRole('eksekutif') ? 'emerald' : 'zinc');
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6 max-w-7xl mx-auto">
        {{-- Header Dashboard & Theme Toggle --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white leading-tight">
                        Sistem Informasi Eksekutif &mdash; Analitik SMS A2P
                    </h1>
                    <flux:badge size="sm" :color="$roleColor" icon="shield-check">
                        {{ $roleLabel }}
                    </flux:badge>
                </div>
                <p class="text-sm text-slate-500 dark:text-zinc-400">
                    Ringkasan trafik pengiriman SMS A2P dan performa operator telekomunikasi PT Triple One Global.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto print:hidden" x-data>
                <flux:button
                    variant="subtle"
                    size="sm"
                    x-on:click="$flux.dark = ! $flux.dark"
                    aria-label="Ubah tema Light/Dark"
                    class="border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-xs"
                >
                    <span class="inline-flex items-center gap-2">
                        <flux:icon name="sun" class="size-4 hidden dark:block text-amber-400" />
                        <flux:icon name="moon" class="size-4 block dark:hidden text-slate-700" />
                        <span class="text-xs font-medium text-slate-700 dark:text-zinc-200" x-text="$flux.dark ? 'Light Mode' : 'Dark Mode'"></span>
                    </span>
                </flux:button>
            </div>
        </div>

        {{-- Komponen Utama Analytics --}}
        <livewire:dashboard-analytics />
    </div>
</x-layouts::app>

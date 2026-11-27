<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased selection:bg-royal-600 selection:text-white">
        <div class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden p-6 md:p-10">
            {{-- Subtle Dot Pattern & Ambient Glow --}}
            <div
                class="pointer-events-none absolute inset-0 -z-20 opacity-30 dark:opacity-10"
                style="background-image: radial-gradient(#3b82f6 0.6px, transparent 0.6px); background-size: 28px 28px;"
                aria-hidden="true"
            ></div>
            <div class="pointer-events-none absolute -top-32 left-1/3 -z-10 h-96 w-96 rounded-full bg-royal-600/15 blur-[100px] dark:bg-royal-600/10" aria-hidden="true"></div>

            {{-- Theme Toggle (Top Right) --}}
            <div class="fixed top-4 right-4 z-20" x-data>
                <flux:button
                    variant="subtle"
                    size="sm"
                    x-on:click="$flux.dark = ! $flux.dark"
                    aria-label="Ubah tema Light/Dark"
                    class="border border-slate-200 dark:border-zinc-700 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm"
                >
                    <flux:icon name="sun" class="size-4 hidden dark:block text-amber-400" />
                    <flux:icon name="moon" class="size-4 block dark:hidden text-navy-900" />
                </flux:button>
            </div>

            <div class="flex w-full max-w-md flex-col gap-6">
                {{-- Brand Header --}}
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2.5 group" wire:navigate>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-royal-600 to-blue-500 text-white shadow-lg shadow-royal-600/25 transition-transform duration-300 group-hover:scale-105">
                        <flux:icon name="chart-bar-square" class="size-6" />
                    </div>
                    <div class="text-center">
                        <span class="block text-sm font-bold tracking-tight text-navy-900 dark:text-white">
                            SIE SMS A2P
                        </span>
                        <span class="block text-xs text-slate-500 dark:text-zinc-400">
                            PT Triple One Global
                        </span>
                    </div>
                </a>

                {{-- Auth Surface Card --}}
                <div class="rounded-2xl border border-blue-100/80 dark:border-zinc-800/80 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 dark:shadow-black/30 ring-1 ring-black/[.02] dark:ring-white/[.02]">
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>

                <p class="text-center text-xs text-slate-500 dark:text-zinc-500">
                    &copy; {{ date('Y') }} PT Triple One Global &mdash; Sistem Informasi Eksekutif
                </p>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

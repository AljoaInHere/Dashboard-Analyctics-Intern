@php
    $operatorPalette = ['#1d4ed8', '#3b82f6', '#38bdf8', '#7dd3fc', '#5b6fd8'];
@endphp

<div
    class="space-y-6"
    x-data="{
        slideState: 'idle',
        slideTimer: null,
        triggerSlide(dir) {
            clearTimeout(this.slideTimer);
            this.slideState = dir === 'prev' ? 'enter-prev' : 'enter-next';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    this.slideState = 'idle';
                });
            });
        }
    }"
    x-on:period-slid.window="triggerSlide($event.detail?.direction ?? $event.detail?.[0]?.direction ?? 'next')"
>
    {{-- Role-based Action Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div class="flex flex-wrap items-center gap-2.5">
            @if ($canWrite)
                <flux:button
                    size="sm"
                    variant="primary"
                    icon="plus"
                    wire:click="openInputModal"
                    class="shadow-sm"
                    style="background-color: #2563eb; color: #ffffff; border-color: #2563eb;"
                >
                    Input Data
                </flux:button>
                <flux:button
                    size="sm"
                    variant="subtle"
                    icon="arrow-up-tray"
                    wire:click="openImportModal"
                    class="border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900"
                >
                    Import CSV
                </flux:button>
                <flux:dropdown>
                    <flux:button
                        size="sm"
                        variant="subtle"
                        icon="arrow-down-tray"
                        icon-trailing="chevron-down"
                        class="border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900"
                    >
                        Ekspor
                    </flux:button>
                    <flux:menu>
                        <flux:menu.item icon="document-arrow-down" wire:click="exportCsv">
                            Ekspor CSV (Filter Aktif)
                        </flux:menu.item>
                        <flux:menu.item icon="printer" x-on:click="window.print()">
                            Cetak / Simpan PDF
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            @else
                <flux:badge size="sm" color="zinc" icon="eye">
                    Mode View-Only (Pengguna Umum)
                </flux:badge>
            @endif
        </div>

        <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-3 py-1 text-xs text-slate-500 dark:text-zinc-400">
            <flux:icon name="calendar-days" class="size-3.5 text-blue-600 dark:text-sky-400" />
            <span>Menampilkan:</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</span>
            <span>&ndash;</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
        </div>
    </div>

    {{-- Filter Bar Inline (1 Baris Rapi + Animasi Slide Pill & Navigasi Hari) --}}
    <div class="rounded-2xl bg-white dark:bg-zinc-900 p-4 shadow-sm dark:shadow-none border border-slate-200 dark:border-zinc-800">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            {{-- Segmented Preset Pills dengan Indikator Slide Super Mulus (iOS-style Spring Physics) --}}
            <div
                x-ref="presetBar"
                x-data="{
                    activeKey: @js($preset),
                    ready: false,
                    moveTo(key, withAnim = true) {
                        if (key !== undefined && key !== null) this.activeKey = key;
                        const container = this.$refs.presetBar;
                        const pill = this.$refs.slidePill;
                        if (!container || !pill) return;

                        const btn = container.querySelector(`[data-preset='${this.activeKey}']`);
                        if (!btn) {
                            this.ready = false;
                            pill.style.opacity = '0';
                            return;
                        }

                        const shouldAnimate = withAnim && this.ready;
                        pill.style.transition = shouldAnimate
                            ? 'transform 0.42s cubic-bezier(0.32, 0.72, 0, 1), width 0.42s cubic-bezier(0.32, 0.72, 0, 1), height 0.42s cubic-bezier(0.32, 0.72, 0, 1), opacity 0.2s ease'
                            : 'none';
                        pill.style.width = `${btn.offsetWidth}px`;
                        pill.style.height = `${btn.offsetHeight}px`;
                        pill.style.transform = `translate3d(${btn.offsetLeft}px, ${btn.offsetTop}px, 0)`;
                        pill.style.opacity = '1';
                        this.ready = true;
                    }
                }"
                x-init="
                    $nextTick(() => {
                        moveTo($wire.preset, false);
                        requestAnimationFrame(() => moveTo($wire.preset, false));
                    });
                    $watch('$wire.preset', (val) => {
                        if (val === activeKey) return;
                        moveTo(val, true);
                    });
                "
                x-on:resize.window="moveTo(activeKey, false)"
                class="inline-flex shrink-0 self-start lg:self-center flex-wrap items-center rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800"
                style="position: relative; padding: 4px; gap: 4px;"
            >
                {{-- Sliding Active Royal Blue Pill (wire:ignore agar Livewire tidak memotong animasi) --}}
                <div
                    wire:ignore
                    x-ref="slidePill"
                    aria-hidden="true"
                    class="pointer-events-none"
                    style="position: absolute; top: 0; left: 0; z-index: 1; opacity: 0; border-radius: 8px; background-color: #2563eb; box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35); will-change: transform, width;"
                ></div>

                @foreach ([
                    'today' => 'Hari Ini',
                    '7_days' => '7 Hari',
                    '30_days' => '30 Hari',
                    'this_month' => 'Bulan Ini',
                    'all' => 'Semua',
                ] as $key => $label)
                    <button
                        type="button"
                        data-preset="{{ $key }}"
                        wire:key="preset-{{ $key }}"
                        x-on:click="moveTo('{{ $key }}', true)"
                        wire:click="setPreset('{{ $key }}')"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap cursor-pointer"
                        :class="activeKey === '{{ $key }}' ? 'text-white' : 'text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white'"
                        :style="`position: relative; z-index: 2; transition: color 0.32s cubic-bezier(0.32, 0.72, 0, 1); ${activeKey === '{{ $key }}' ? 'color: #ffffff;' : ''}`"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Rentang Tanggal Kustom + Tombol Slide Hari Sebelumnya/Berikutnya & Operator --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        wire:click="shiftPeriod(-1)"
                        title="Geser ke periode/hari sebelumnya"
                        aria-label="Periode sebelumnya"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 dark:hover:text-sky-300 transition-colors cursor-pointer"
                        style="width: 32px; height: 32px;"
                    >
                        <flux:icon name="chevron-left" class="size-4" />
                    </button>

                    <div class="w-36">
                        <flux:input
                            type="date"
                            size="sm"
                            wire:model.live="startDate"
                            aria-label="Tanggal mulai"
                        />
                    </div>
                    <span class="text-xs font-medium text-slate-400 dark:text-zinc-500">s/d</span>
                    <div class="w-36">
                        <flux:input
                            type="date"
                            size="sm"
                            wire:model.live="endDate"
                            aria-label="Tanggal selesai"
                        />
                    </div>

                    <button
                        type="button"
                        wire:click="shiftPeriod(1)"
                        title="Geser ke periode/hari berikutnya"
                        aria-label="Periode berikutnya"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-zinc-700 dark:hover:text-sky-300 transition-colors cursor-pointer"
                        style="width: 32px; height: 32px;"
                    >
                        <flux:icon name="chevron-right" class="size-4" />
                    </button>
                </div>

                <div class="w-40">
                    <flux:select size="sm" wire:model.live="operator" aria-label="Filter operator">
                        <flux:select.option value="all">Semua Operator</flux:select.option>
                        @foreach ($availableOperators as $op)
                            <flux:select.option wire:key="opt-{{ $op }}" value="{{ $op }}">
                                {{ $op }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div wire:loading.flex class="items-center gap-1.5 text-xs font-medium text-blue-600 dark:text-sky-400">
                    <flux:icon name="arrow-path" class="size-4 animate-spin" />
                    <span>Memuat...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Metrik KPI Utama (2 Kolom Proporsional dengan Transisi Slide Halus) --}}
    <div
        class="grid grid-cols-1 md:grid-cols-2 gap-5"
        wire:loading.class="opacity-60 pointer-events-none"
        wire:target="setPreset, shiftPeriod, resetFilters, startDate, endDate, operator"
        :style="slideState === 'enter-next'
            ? 'transform: translateX(22px); opacity: 0.45; transition: none;'
            : (slideState === 'enter-prev'
                ? 'transform: translateX(-22px); opacity: 0.45; transition: none;'
                : 'transform: translateX(0); opacity: 1; transition: transform 0.34s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.34s ease;')"
    >
        {{-- KARTU 1: Total Dikirim --}}
        <div class="group rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm dark:shadow-none border border-slate-200 dark:border-zinc-800 flex flex-col justify-between gap-4 transition-all duration-200 hover:shadow-md hover:border-blue-300 dark:hover:border-zinc-700">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                        Total Dikirim
                    </p>
                    <div class="flex flex-wrap items-baseline gap-3">
                        <h3
                            class="font-bold tracking-tight text-slate-900 dark:text-white tabular-nums"
                            style="font-size: 2.125rem; line-height: 1.1;"
                        >
                            {{ number_format($totalBulanIni) }}
                        </h3>
                        @if ($sentDelta !== null)
                            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $sentDelta >= 0 ? 'border-emerald-200 dark:border-emerald-500/40 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'border-rose-200 dark:border-rose-500/40 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' }}">
                                <flux:icon name="{{ $sentDelta >= 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" class="size-3.5" />
                                {{ $sentDelta >= 0 ? '+' : '' }}{{ $sentDelta }}%
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 dark:bg-zinc-800 text-blue-600 dark:text-sky-400 transition-transform duration-200 group-hover:scale-105">
                    <flux:icon name="paper-airplane" class="size-5" />
                </div>
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 dark:border-zinc-800 text-xs text-slate-500 dark:text-zinc-400"
                style="padding-top: 14px; margin-top: 4px;"
            >
                <span>
                    {{ $sentDelta !== null ? 'Perbandingan terhadap periode sebelumnya' : 'Belum ada data pembanding periode sebelumnya' }}
                </span>
                <span class="inline-flex items-center gap-1.5 font-semibold text-indigo-600 dark:text-indigo-300 tabular-nums">
                    <span class="rounded-full shrink-0" style="width: 7px; height: 7px; background-color: #818cf8;"></span>
                    SMS Gagal: {{ number_format($totalGagal) }}
                </span>
            </div>
        </div>

        {{-- KARTU 2: Delivery Rate --}}
        <div class="group rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm dark:shadow-none border border-slate-200 dark:border-zinc-800 flex flex-col justify-between gap-4 transition-all duration-200 hover:shadow-md hover:border-emerald-300 dark:hover:border-zinc-700">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                        Delivery Rate
                    </p>
                    <div class="flex flex-wrap items-baseline gap-3">
                        <h3
                            class="inline-flex items-baseline font-bold tracking-tight text-slate-900 dark:text-white tabular-nums"
                            style="font-size: 2.125rem; line-height: 1.1;"
                        >
                            <span>{{ $deliveryRate }}</span><span class="text-lg font-semibold text-blue-600 dark:text-sky-400" style="margin-left: 4px;">%</span>
                        </h3>
                        <flux:badge size="sm" color="emerald" icon="check-circle">
                            {{ number_format($totalSukses) }} sukses
                        </flux:badge>
                        @if ($rateDelta !== null)
                            <span class="text-xs font-semibold {{ $rateDelta >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $rateDelta >= 0 ? '+' : '' }}{{ $rateDelta }} % pt
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 transition-transform duration-200 group-hover:scale-105">
                    <flux:icon name="check-badge" class="size-5" />
                </div>
            </div>

            <div
                class="space-y-2.5 border-t border-slate-200 dark:border-zinc-800"
                style="display: flex; flex-direction: column; gap: 10px; padding-top: 14px; margin-top: 4px;"
            >
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-zinc-400">
                    <span>Rasio Keberhasilan</span>
                    <span class="font-semibold text-slate-900 dark:text-white tabular-nums">{{ $deliveryRate }} % / 100 %</span>
                </div>
                <div class="w-full overflow-hidden rounded-full bg-slate-200 dark:bg-zinc-800" style="height: 10px;">
                    <div
                        class="rounded-full transition-all duration-700"
                        style="height: 10px; width: {{ min(100, max(0, $deliveryRate)) }}%; background: linear-gradient(90deg, #2563eb, #38bdf8);"
                    ></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Seksi Grafik --}}
    <div
        class="grid grid-cols-1 lg:grid-cols-3 gap-5"
        wire:loading.class="opacity-60 pointer-events-none"
        wire:target="setPreset, shiftPeriod, resetFilters, startDate, endDate, operator"
        :style="slideState === 'enter-next'
            ? 'transform: translateX(26px); opacity: 0.45; transition: none;'
            : (slideState === 'enter-prev'
                ? 'transform: translateX(-26px); opacity: 0.45; transition: none;'
                : 'transform: translateX(0); opacity: 1; transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.38s ease;')"
    >
        {{-- Grafik Tren --}}
        <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm dark:shadow-none border border-slate-200 dark:border-zinc-800 flex flex-col justify-between transition-all duration-200 hover:shadow-md">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg" class="text-slate-900 dark:text-white">
                        Tren Trafik SMS
                    </flux:heading>
                    <flux:text class="text-xs text-slate-500 dark:text-zinc-400">
                        Fluktuasi SMS dikirim vs gagal pada periode aktif
                    </flux:text>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-medium border border-blue-200 dark:border-blue-500/30">
                        <span class="rounded-full shrink-0" style="width: 8px; height: 8px; background-color: #2563eb;"></span> Dikirim
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-medium border border-indigo-200 dark:border-indigo-400/30">
                        <span class="rounded-full shrink-0" style="width: 8px; height: 8px; background-color: #818cf8;"></span> Gagal
                    </span>
                </div>
            </div>

            @if ($labelsTren->isEmpty())
                <div class="flex h-72 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950/40 text-center p-6 gap-3">
                    <flux:icon name="chart-bar-square" class="size-10 text-slate-400 dark:text-zinc-600" />
                    <div class="space-y-1">
                        <flux:text class="text-sm font-semibold text-slate-700 dark:text-zinc-200">
                            Tidak ada data tren pada periode ini
                        </flux:text>
                        <flux:text class="text-xs text-slate-500 dark:text-zinc-400">
                            Ubah rentang tanggal atau tampilkan seluruh data yang tersedia.
                        </flux:text>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                        <flux:button size="sm" variant="subtle" icon="arrow-path" wire:click="resetFilters" class="border border-slate-200 dark:border-zinc-700">
                            Tampilkan Semua Data
                        </flux:button>
                        @if ($canWrite)
                            <flux:button size="sm" variant="primary" icon="plus" wire:click="openInputModal" style="background-color: #2563eb; color: #ffffff;">
                                Input Data
                            </flux:button>
                        @endif
                    </div>
                </div>
            @else
                <div
                    wire:ignore
                    wire:key="trend-chart-active"
                    x-data="analyticsTrendChart(@js($labelsTren->values()), @js($dataTren->values()), @js($failedTren->values()))"
                    x-init="init()"
                    x-on:charts-updated.window="updateChart($event.detail[0] ?? $event.detail)"
                    class="relative h-72 w-full mt-auto"
                >
                    <canvas x-ref="canvas"></canvas>
                </div>
            @endif
        </div>

        {{-- Grafik Distribusi Operator --}}
        <div class="lg:col-span-1 rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm dark:shadow-none border border-slate-200 dark:border-zinc-800 flex flex-col justify-between transition-all duration-200 hover:shadow-md">
            <div class="mb-4">
                <flux:heading size="lg" class="text-slate-900 dark:text-white">
                    Distribusi per Operator
                </flux:heading>
                <flux:text class="text-xs text-slate-500 dark:text-zinc-400">
                    Pangsa volume SMS per operator
                </flux:text>
            </div>

            @if ($labelsOperator->isEmpty())
                <div class="flex h-72 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950/40 text-center p-6 gap-2.5">
                    <flux:icon name="signal" class="size-10 text-slate-400 dark:text-zinc-600" />
                    <flux:text class="text-sm font-semibold text-slate-700 dark:text-zinc-200">
                        Tidak ada data operator
                    </flux:text>
                    <flux:text class="text-xs text-slate-500 dark:text-zinc-400">
                        Data pangsa operator akan muncul otomatis saat data tersedia.
                    </flux:text>
                </div>
            @else
                @php
                    $operatorTotal = $dataOperator->sum();
                    $operatorCount = $labelsOperator->count();
                    $legendTextClass = match (true) {
                        $operatorCount <= 3 => 'text-sm',
                        $operatorCount <= 5 => 'text-xs',
                        default => 'text-[11px]',
                    };
                    $legendSpaceClass = match (true) {
                        $operatorCount <= 3 => 'space-y-2.5',
                        $operatorCount <= 5 => 'space-y-2',
                        default => 'space-y-1.5',
                    };
                @endphp
                <div
                    wire:ignore
                    wire:key="operator-chart-active"
                    x-data="analyticsOperatorChart(@js($labelsOperator->values()), @js($dataOperator->values()), @js($operatorPalette))"
                    x-init="init()"
                    x-on:charts-updated.window="updateChart($event.detail[0] ?? $event.detail)"
                    class="relative mx-auto h-56 w-full max-w-[230px]"
                >
                    <canvas x-ref="canvas"></canvas>
                </div>

                <div
                    class="mt-5 {{ $legendSpaceClass }} border-t border-slate-200 dark:border-zinc-800"
                    style="padding-top: 14px;"
                >
                    @foreach ($labelsOperator as $i => $label)
                        @php
                            $opShare = $operatorTotal > 0 ? round(($dataOperator[$i] / $operatorTotal) * 100, 1) : 0;
                            $isSelectedOp = $operator === $label;
                        @endphp
                        <button
                            type="button"
                            wire:key="op-legend-{{ $i }}"
                            wire:click="selectOperator('{{ $label }}')"
                            title="{{ $isSelectedOp ? 'Klik untuk menampilkan semua operator' : 'Klik untuk filter operator ' . $label }}"
                            class="w-full flex items-center justify-between gap-2 rounded-lg px-2 py-1 text-left transition-colors cursor-pointer {{ $legendTextClass }} {{ $isSelectedOp ? 'bg-blue-50 dark:bg-blue-950/50 ring-1 ring-blue-500/40' : 'hover:bg-slate-50 dark:hover:bg-zinc-800/60' }}"
                        >
                            <span class="flex items-center gap-2 truncate text-slate-600 dark:text-zinc-300">
                                <span
                                    class="rounded-full shrink-0"
                                    style="width: 10px; height: 10px; background-color: {{ $operatorPalette[$i % count($operatorPalette)] }};"
                                ></span>
                                <span class="truncate {{ $isSelectedOp ? 'font-semibold text-blue-700 dark:text-sky-300' : '' }}">{{ $label }}</span>
                            </span>
                            <span class="shrink-0 font-semibold text-slate-900 dark:text-white tabular-nums">
                                {{ number_format($dataOperator[$i]) }}
                                <span class="font-normal text-slate-400 dark:text-zinc-500">({{ $opShare }} %)</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tabel Rincian Harian --}}
    <div
        class="rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm dark:shadow-none border border-slate-200 dark:border-zinc-800 space-y-4 transition-opacity duration-200"
        wire:loading.class="opacity-60 pointer-events-none"
        wire:target="setPreset, shiftPeriod, resetFilters, startDate, endDate, operator, sort, gotoPage, nextPage, previousPage"
        :style="slideState === 'enter-next'
            ? 'transform: translateX(22px); opacity: 0.45; transition: none;'
            : (slideState === 'enter-prev'
                ? 'transform: translateX(-22px); opacity: 0.45; transition: none;'
                : 'transform: translateX(0); opacity: 1; transition: transform 0.36s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.36s ease;')"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="lg" class="text-slate-900 dark:text-white">
                    Rincian Trafik Harian
                </flux:heading>
                <flux:text class="text-xs text-slate-500 dark:text-zinc-400">
                    Klik header Operator untuk memilih operator atau klik tanggal pada baris untuk memfokuskan hari tertentu
                </flux:text>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($operator !== 'all' || ($preset === 'custom' && $startDate === $endDate))
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 dark:border-blue-500/40 bg-blue-50 dark:bg-blue-950/60 px-3 py-1 text-xs font-medium text-blue-700 dark:text-sky-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-colors cursor-pointer"
                    >
                        <span>Reset Pilihan</span>
                        <flux:icon name="x-mark" class="size-3.5" />
                    </button>
                @endif
                <flux:badge size="sm" color="zinc" icon="table-cells">
                    {{ $records->total() }} baris data
                </flux:badge>
            </div>
        </div>

        @if ($records->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950/40 py-10 px-6 text-center gap-3">
                <flux:icon name="document-magnifying-glass" class="size-10 text-slate-400 dark:text-zinc-600" />
                <div class="space-y-1">
                    <flux:text class="text-sm font-semibold text-slate-700 dark:text-zinc-200">
                        Belum ada catatan trafik SMS untuk filter ini
                    </flux:text>
                    <flux:text class="text-xs text-slate-500 dark:text-zinc-400">
                        Sesuaikan rentang tanggal atau reset filter untuk melihat seluruh data.
                    </flux:text>
                </div>
                <flux:button size="sm" variant="subtle" icon="arrow-path" wire:click="resetFilters" class="border border-slate-200 dark:border-zinc-700">
                    Reset Filter
                </flux:button>
            </div>
        @else
            <flux:table :paginate="$records">
                <flux:table.columns>
                    <flux:table.column>
                        <flux:dropdown>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 font-medium transition-colors cursor-pointer {{ $sortBy === 'tanggal' ? 'text-blue-600 dark:text-sky-400 font-semibold' : 'text-inherit hover:text-blue-600 dark:hover:text-sky-400' }}"
                            >
                                <span>Tanggal</span>
                                <flux:icon name="chevron-down" class="size-3.5" />
                            </button>
                            <flux:menu>
                                <flux:menu.item
                                    :icon="$sortBy === 'tanggal' && $sortDirection === 'desc' ? 'check' : null"
                                    wire:click="setSort('tanggal', 'desc')"
                                >
                                    Terbaru ke Terlama
                                </flux:menu.item>
                                <flux:menu.item
                                    :icon="$sortBy === 'tanggal' && $sortDirection === 'asc' ? 'check' : null"
                                    wire:click="setSort('tanggal', 'asc')"
                                >
                                    Terlama ke Terbaru
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item
                                    :icon="$sortBy === 'total_dikirim' && $sortDirection === 'desc' ? 'check' : null"
                                    wire:click="setSort('total_dikirim', 'desc')"
                                >
                                    Total Dikirim Terbanyak
                                </flux:menu.item>
                                <flux:menu.item
                                    :icon="$sortBy === 'total_dikirim' && $sortDirection === 'asc' ? 'check' : null"
                                    wire:click="setSort('total_dikirim', 'asc')"
                                >
                                    Total Dikirim Terendah
                                </flux:menu.item>
                                <flux:menu.item
                                    :icon="$sortBy === 'total_sukses' && $sortDirection === 'desc' ? 'check' : null"
                                    wire:click="setSort('total_sukses', 'desc')"
                                >
                                    SMS Sukses Terbanyak
                                </flux:menu.item>
                                <flux:menu.item
                                    :icon="$sortBy === 'total_gagal' && $sortDirection === 'desc' ? 'check' : null"
                                    wire:click="setSort('total_gagal', 'desc')"
                                >
                                    SMS Gagal Terbanyak
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.column>
                    <flux:table.column>
                        <flux:dropdown>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 font-medium transition-colors cursor-pointer {{ $operator !== 'all' ? 'text-blue-600 dark:text-sky-400 font-semibold' : 'text-inherit hover:text-blue-600 dark:hover:text-sky-400' }}"
                            >
                                <span>{{ $operator !== 'all' ? 'Operator: ' . $operator : 'Operator' }}</span>
                                <flux:icon name="chevron-down" class="size-3.5" />
                            </button>
                            <flux:menu>
                                <flux:menu.item
                                    :icon="$operator === 'all' ? 'check' : null"
                                    wire:click="$set('operator', 'all')"
                                >
                                    Semua Operator
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach ($availableOperators as $op)
                                    <flux:menu.item
                                        wire:key="col-op-{{ $op }}"
                                        :icon="$operator === $op ? 'check' : null"
                                        wire:click="$set('operator', '{{ $op }}')"
                                    >
                                        {{ $op }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'total_dikirim'" :direction="$sortDirection" wire:click="sort('total_dikirim')" align="end">
                        Total Dikirim
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'total_sukses'" :direction="$sortDirection" wire:click="sort('total_sukses')" align="end">
                        Sukses
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'total_gagal'" :direction="$sortDirection" wire:click="sort('total_gagal')" align="end">
                        Gagal
                    </flux:table.column>
                    <flux:table.column align="end">
                        Delivery Rate
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($records as $row)
                        @php
                            $rate = $row->delivery_rate;
                            $badgeColor = $rate >= 95 ? 'emerald' : ($rate >= 85 ? 'amber' : 'rose');
                            $rowDate = $row->tanggal->toDateString();
                            $isDateSelected = $preset === 'custom' && $startDate === $rowDate && $endDate === $rowDate;
                            $isOpSelected = $operator === $row->operator;
                        @endphp
                        <flux:table.row wire:key="traffic-row-{{ $row->id }}">
                            <flux:table.cell class="whitespace-nowrap font-medium">
                                <button
                                    type="button"
                                    wire:click="selectDate('{{ $rowDate }}')"
                                    title="{{ $isDateSelected ? 'Klik untuk menampilkan semua tanggal' : 'Klik untuk filter tanggal ' . $row->tanggal->format('d M Y') }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-left transition-colors cursor-pointer {{ $isDateSelected ? 'bg-blue-600 text-white font-semibold' : 'text-slate-800 dark:text-zinc-100 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-zinc-800 dark:hover:text-sky-300' }}"
                                    @if ($isDateSelected) style="background-color: #2563eb; color: #ffffff;" @endif
                                >
                                    <flux:icon name="calendar" class="size-3.5 opacity-75" />
                                    <span>{{ $row->tanggal->format('d M Y') }}</span>
                                </button>
                            </flux:table.cell>
                            <flux:table.cell>
                                <button
                                    type="button"
                                    wire:click="selectOperator('{{ $row->operator }}')"
                                    title="{{ $isOpSelected ? 'Klik untuk menampilkan semua operator' : 'Klik untuk filter operator ' . $row->operator }}"
                                    class="inline-flex items-center rounded-full transition-transform hover:scale-105 cursor-pointer {{ $isOpSelected ? 'ring-2 ring-blue-500 ring-offset-1 dark:ring-offset-zinc-900' : '' }}"
                                >
                                    <flux:badge size="sm" :color="$isOpSelected ? 'sky' : 'blue'" inset="top bottom">
                                        {{ $row->operator }}
                                    </flux:badge>
                                </button>
                            </flux:table.cell>
                            <flux:table.cell align="end" variant="strong" class="tabular-nums">
                                {{ number_format($row->total_dikirim) }}
                            </flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums text-emerald-600 dark:text-emerald-400">
                                {{ number_format($row->total_sukses) }}
                            </flux:table.cell>
                            <flux:table.cell align="end" class="tabular-nums text-indigo-600 dark:text-indigo-300">
                                {{ number_format($row->total_gagal) }}
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:badge size="sm" :color="$badgeColor" inset="top bottom">
                                    {{ number_format($rate, 1) }} %
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>

    {{-- Modal Input Data Manual --}}
    @if ($canWrite)
        <flux:modal wire:model="showInputModal" class="md:w-[500px]">
            <form wire:submit="saveTraffic" class="space-y-5">
                <div>
                    <flux:heading size="lg">Input Data Trafik SMS</flux:heading>
                    <flux:text class="mt-1 text-xs">
                        Pastikan Total Sukses + Total Gagal sama dengan Total Dikirim.
                    </flux:text>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Tanggal</flux:label>
                        <flux:input type="date" wire:model="inputTanggal" required />
                        <flux:error name="inputTanggal" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Operator</flux:label>
                        <flux:input type="text" wire:model="inputOperator" placeholder="cth: Telkomsel" required />
                        <flux:error name="inputOperator" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>Total Dikirim</flux:label>
                        <flux:input type="number" min="0" wire:model.live="inputTotalDikirim" required />
                        <flux:error name="inputTotalDikirim" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Total Sukses</flux:label>
                        <flux:input type="number" min="0" wire:model.live="inputTotalSukses" required />
                        <flux:error name="inputTotalSukses" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Total Gagal</flux:label>
                        <flux:input type="number" min="0" wire:model.live="inputTotalGagal" required />
                        <flux:error name="inputTotalGagal" />
                    </flux:field>
                </div>

                @php
                    $previewSent = (int) $inputTotalDikirim;
                    $previewSuccess = (int) $inputTotalSukses;
                    $previewFailed = (int) $inputTotalGagal;
                    $previewRate = $previewSent > 0 ? round(($previewSuccess / $previewSent) * 100, 2) : 0;
                    $isBalanced = $previewSent > 0 && ($previewSuccess + $previewFailed === $previewSent);
                @endphp

                <div class="rounded-xl border border-blue-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50 p-3 flex items-center justify-between text-xs">
                    <span class="text-slate-600 dark:text-zinc-300">Preview Delivery Rate:</span>
                    <div class="flex items-center gap-2">
                        <flux:badge size="sm" :color="$isBalanced ? 'emerald' : 'amber'">
                            {{ $previewRate }}%
                        </flux:badge>
                        @if ($previewSent > 0 && ! $isBalanced)
                            <span class="text-rose-600 dark:text-rose-400 font-medium">Selisih: {{ $previewSent - ($previewSuccess + $previewFailed) }}</span>
                        @endif
                    </div>
                </div>

                @if ($confirmOverwrite)
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.heading>Data sudah ada</flux:callout.heading>
                        <flux:callout.text>
                            Data untuk <strong>{{ $inputOperator }}</strong> tanggal <strong>{{ $inputTanggal }}</strong> sudah tercatat. Timpa data lama?
                        </flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button variant="ghost" type="button" wire:click="$set('showInputModal', false)">
                        Batal
                    </flux:button>
                    @if ($confirmOverwrite)
                        <flux:button variant="danger" type="button" wire:click="saveTraffic(true)">
                            Timpa Data Lama
                        </flux:button>
                    @else
                        <flux:button variant="primary" type="submit">
                            Simpan Data
                        </flux:button>
                    @endif
                </div>
            </form>
        </flux:modal>

        {{-- Modal Import CSV --}}
        <flux:modal wire:model="showImportModal" class="md:w-[680px]">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">Import Data Trafik (CSV)</flux:heading>
                    <flux:text class="mt-1 text-xs">
                        Format kolom wajib: <code class="px-1 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800">tanggal,operator,total_dikirim,total_sukses,total_gagal</code>
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>Pilih Berkas CSV</flux:label>
                    <flux:input type="file" accept=".csv,text/csv" wire:model="csvFile" />
                    <flux:error name="csvFile" />
                </flux:field>

                @if (! empty($importPreview))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-600 dark:text-zinc-300">
                                Preview ({{ count($importPreview) }} baris pertama)
                            </span>
                            <flux:checkbox wire:model="importOverwriteDuplicates" label="Timpa data duplikat" />
                        </div>

                        <div class="max-h-60 overflow-y-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-3 py-2">Tanggal</th>
                                        <th class="px-3 py-2">Operator</th>
                                        <th class="px-3 py-2 text-right">Dikirim</th>
                                        <th class="px-3 py-2 text-right">Sukses</th>
                                        <th class="px-3 py-2 text-right">Gagal</th>
                                        <th class="px-3 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach ($importPreview as $idx => $item)
                                        <tr wire:key="csv-prev-{{ $idx }}">
                                            <td class="px-3 py-2">{{ $item['tanggal'] }}</td>
                                            <td class="px-3 py-2">{{ $item['operator'] }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($item['total_dikirim']) }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($item['total_sukses']) }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($item['total_gagal']) }}</td>
                                            <td class="px-3 py-2">
                                                @if ($item['error'])
                                                    <flux:badge size="sm" color="rose">{{ $item['error'] }}</flux:badge>
                                                @elseif ($item['duplicate'])
                                                    <flux:badge size="sm" color="amber">Duplikat</flux:badge>
                                                @else
                                                    <flux:badge size="sm" color="emerald">Siap</flux:badge>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($importSummary !== null)
                    <flux:callout variant="success" icon="check-circle">
                        <flux:callout.heading>Hasil Import CSV</flux:callout.heading>
                        <flux:callout.text>
                            Berhasil ditambah: <strong>{{ $importSummary['imported'] }}</strong>,
                            Diperbarui: <strong>{{ $importSummary['updated'] }}</strong>,
                            Dilewati: <strong>{{ $importSummary['skipped'] }}</strong>.
                        </flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <flux:button variant="ghost" type="button" wire:click="$set('showImportModal', false)">
                        Tutup
                    </flux:button>
                    @if (! empty($importPreview))
                        <flux:button variant="primary" type="button" wire:click="confirmImport" icon="check">
                            Konfirmasi Import
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:modal>
    @endif
</div>

@script
<script>
    Alpine.data('analyticsTrendChart', (labels, sentData, failedData) => ({
        chartInstance: null,
        observer: null,

        themeColors() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                tick: isDark ? '#a1a1aa' : '#64748b',
                grid: isDark ? 'rgba(255, 255, 255, 0.06)' : '#eef4ff',
            };
        },

        init() {
            const ctx = this.$refs.canvas.getContext('2d');
            const colors = this.themeColors();

            const gradient = ctx.createLinearGradient(0, 0, 0, 280);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.24)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

            const failedGradient = ctx.createLinearGradient(0, 0, 0, 280);
            failedGradient.addColorStop(0, 'rgba(129, 140, 248, 0.14)');
            failedGradient.addColorStop(1, 'rgba(129, 140, 248, 0)');

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'SMS Dikirim',
                            data: sentData,
                            borderColor: '#2563eb',
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#2563eb',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            borderWidth: 2.5,
                        },
                        {
                            label: 'SMS Gagal',
                            data: failedData,
                            borderColor: '#818cf8',
                            backgroundColor: failedGradient,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#818cf8',
                            pointRadius: 3.5,
                            pointHoverRadius: 5.5,
                            borderWidth: 2,
                            borderDash: [5, 4],
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f2557',
                            titleColor: '#ffffff',
                            bodyColor: '#7dd3fc',
                            borderColor: '#2563eb',
                            borderWidth: 1,
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: true,
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: colors.tick },
                        },
                        y: {
                            grid: { color: colors.grid },
                            ticks: { color: colors.tick },
                            beginAtZero: true,
                        },
                    },
                    animation: { duration: 600, easing: 'easeOutQuart' },
                },
            });

            this.observer = new MutationObserver(() => {
                if (!this.chartInstance) return;
                const updated = this.themeColors();
                this.chartInstance.options.scales.x.ticks.color = updated.tick;
                this.chartInstance.options.scales.y.ticks.color = updated.tick;
                this.chartInstance.options.scales.y.grid.color = updated.grid;
                this.chartInstance.update('none');
            });
            this.observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },

        updateChart(payload) {
            if (!this.chartInstance || !payload) return;
            this.chartInstance.data.labels = payload.trendLabels ?? [];
            this.chartInstance.data.datasets[0].data = payload.trendSent ?? [];
            this.chartInstance.data.datasets[1].data = payload.trendFailed ?? [];
            this.chartInstance.update();
        },

        destroy() {
            this.observer?.disconnect();
            this.chartInstance?.destroy();
        },
    }));

    Alpine.data('analyticsOperatorChart', (labels, data, palette) => ({
        chartInstance: null,
        observer: null,

        borderColor() {
            return document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff';
        },

        init() {
            const ctx = this.$refs.canvas.getContext('2d');
            const bgColors = labels.map((_, i) => palette[i % palette.length]);

            this.chartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: bgColors,
                        borderColor: this.borderColor(),
                        borderWidth: 3,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f2557',
                            titleColor: '#ffffff',
                            bodyColor: '#7dd3fc',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: (context) => {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                    return ` ${context.label}: ${value.toLocaleString()} (${pct}%)`;
                                },
                            },
                        },
                    },
                    animation: { animateRotate: true, animateScale: true, duration: 700 },
                },
            });

            this.observer = new MutationObserver(() => {
                if (!this.chartInstance) return;
                this.chartInstance.data.datasets[0].borderColor = this.borderColor();
                this.chartInstance.update('none');
            });
            this.observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },

        updateChart(payload) {
            if (!this.chartInstance || !payload) return;
            const nextLabels = payload.operatorLabels ?? [];
            const nextData = payload.operatorData ?? [];
            this.chartInstance.data.labels = nextLabels;
            this.chartInstance.data.datasets[0].data = nextData;
            this.chartInstance.data.datasets[0].backgroundColor = nextLabels.map((_, i) => palette[i % palette.length]);
            this.chartInstance.update();
        },

        destroy() {
            this.observer?.disconnect();
            this.chartInstance?.destroy();
        },
    }));
</script>
@endscript

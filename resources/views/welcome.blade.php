<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'Sistem Informasi Eksekutif — SMS A2P'])
    </head>
    <body class="min-h-screen bg-slate-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased selection:bg-blue-600 selection:text-white">
        <div
            class="relative flex min-h-screen flex-col justify-between overflow-hidden"
            x-data="{
                mx: -999,
                my: -999,
                px: 0,
                py: 0,
                active: false,
                onMove(e) {
                    const rect = this.$el.getBoundingClientRect();
                    this.mx = e.clientX - rect.left;
                    this.my = e.clientY - rect.top;
                    this.px = ((e.clientX / window.innerWidth) - 0.5) * -28;
                    this.py = ((e.clientY / window.innerHeight) - 0.5) * -28;
                    this.active = true;
                },
                onLeave() {
                    this.active = false;
                    this.px = 0;
                    this.py = 0;
                }
            }"
            x-on:mousemove="onMove($event)"
            x-on:mouseleave="onLeave()"
        >
            {{-- Interactive Concave / Recessed Dot-Field Canvas (Menjorok ke Dalam di Sekitar Kursor) --}}
            <canvas
                x-ref="dotCanvas"
                x-init="
                    const canvas = $refs.dotCanvas;
                    const ctx = canvas.getContext('2d');
                    const spacing = 28;
                    const radius = 190;
                    let width = 0, height = 0, dpr = 1;
                    let curX = -999, curY = -999;
                    let targetX = -999, targetY = -999;
                    let strength = 0, targetStrength = 0;

                    const resize = () => {
                        const rect = canvas.parentElement.getBoundingClientRect();
                        dpr = Math.min(window.devicePixelRatio || 1, 2);
                        width = rect.width;
                        height = rect.height;
                        canvas.width = Math.floor(width * dpr);
                        canvas.height = Math.floor(height * dpr);
                        canvas.style.width = width + 'px';
                        canvas.style.height = height + 'px';
                        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                    };

                    resize();
                    window.addEventListener('resize', resize);
                    new ResizeObserver(resize).observe(canvas.parentElement);

                    const parent = canvas.parentElement;
                    parent.addEventListener('mousemove', (e) => {
                        const rect = parent.getBoundingClientRect();
                        targetX = e.clientX - rect.left;
                        targetY = e.clientY - rect.top;
                        targetStrength = 1;
                        if (curX < -500) { curX = targetX; curY = targetY; }
                    });
                    parent.addEventListener('mouseleave', () => {
                        targetStrength = 0;
                    });

                    const draw = () => {
                        curX += (targetX - curX) * 0.16;
                        curY += (targetY - curY) * 0.16;
                        strength += (targetStrength - strength) * 0.12;

                        ctx.clearRect(0, 0, width, height);
                        const isDark = document.documentElement.classList.contains('dark');

                        // Soft recessed concave basin shadow under cursor
                        if (strength > 0.01) {
                            const basin = ctx.createRadialGradient(curX, curY, 0, curX, curY, radius);
                            if (isDark) {
                                basin.addColorStop(0, `rgba(2, 6, 23, ${0.55 * strength})`);
                                basin.addColorStop(0.55, `rgba(30, 64, 175, ${0.14 * strength})`);
                                basin.addColorStop(0.82, `rgba(56, 189, 248, ${0.12 * strength})`);
                                basin.addColorStop(1, 'rgba(0, 0, 0, 0)');
                            } else {
                                basin.addColorStop(0, `rgba(30, 58, 138, ${0.16 * strength})`);
                                basin.addColorStop(0.5, `rgba(37, 99, 235, ${0.09 * strength})`);
                                basin.addColorStop(0.82, `rgba(56, 189, 248, ${0.10 * strength})`);
                                basin.addColorStop(1, 'rgba(37, 99, 235, 0)');
                            }
                            ctx.fillStyle = basin;
                            ctx.beginPath();
                            ctx.arc(curX, curY, radius, 0, Math.PI * 2);
                            ctx.fill();
                        }

                        // Draw dot matrix with inward concave pull & depth scaling
                        for (let y = spacing / 2; y < height; y += spacing) {
                            for (let x = spacing / 2; x < width; x += spacing) {
                                const dx = curX - x;
                                const dy = curY - y;
                                const dist = Math.hypot(dx, dy);

                                let drawX = x;
                                let drawY = y;
                                let dotSize = 1.15;
                                let alpha = isDark ? 0.18 : 0.24;
                                let color = isDark ? '59, 130, 246' : '37, 99, 235';

                                if (strength > 0.01 && dist < radius && dist > 0.001) {
                                    const norm = dist / radius; // 0 at center, 1 at edge
                                    const bell = Math.sin(norm * Math.PI); // peaks around rim
                                    const depth = (1 - norm) * (1 - norm); // deepest at center

                                    // Pull dots inward toward center (concave perspective illusion)
                                    const pull = bell * 9.5 * strength;
                                    drawX = x + (dx / dist) * pull;
                                    drawY = y + (dy / dist) * pull;

                                    // Center dots shrink slightly (recessed deeper), rim dots catch highlight
                                    dotSize = 1.15 + (bell * 0.65 - depth * 0.55) * strength;
                                    alpha = (isDark ? 0.18 : 0.24) + (bell * 0.52 + depth * 0.12) * strength;

                                    if (norm > 0.45 && norm < 0.85) {
                                        color = isDark ? '56, 189, 248' : '37, 99, 235';
                                    } else if (norm <= 0.45) {
                                        color = isDark ? '29, 78, 216' : '30, 58, 138';
                                    }
                                }

                                ctx.fillStyle = `rgba(${color}, ${Math.max(0.08, Math.min(0.9, alpha))})`;
                                ctx.beginPath();
                                ctx.arc(drawX, drawY, Math.max(0.55, dotSize), 0, Math.PI * 2);
                                ctx.fill();
                            }
                        }

                        requestAnimationFrame(draw);
                    };

                    requestAnimationFrame(draw);
                "
                class="pointer-events-none absolute inset-0"
                style="z-index: 0;"
                aria-hidden="true"
            ></canvas>

            {{-- Subtle Parallax Ambient Orbs --}}
            <div
                class="pointer-events-none absolute -top-32 left-1/4 h-96 w-96 rounded-full bg-blue-600/15 blur-3xl dark:bg-blue-600/10"
                :style="`z-index: 0; transform: translate3d(${px}px, ${py}px, 0); transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);`"
                aria-hidden="true"
            ></div>
            <div
                class="pointer-events-none absolute top-1/2 -right-24 h-80 w-80 rounded-full bg-sky-400/15 blur-3xl dark:bg-sky-500/10"
                :style="`z-index: 0; transform: translate3d(${px * -0.8}px, ${py * -0.8}px, 0); transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);`"
                aria-hidden="true"
            ></div>

            {{-- Top Bar / Navigasi --}}
            <header class="sticky top-0 z-30 w-full border-b border-slate-200 dark:border-zinc-800 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm transition-transform duration-200 group-hover:scale-105">
                            <flux:icon name="chart-bar-square" class="size-5 text-white" />
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                SIE SMS A2P
                            </span>
                            <span class="hidden sm:inline text-slate-300 dark:text-zinc-700">&mdash;</span>
                            <span class="hidden sm:inline text-xs font-medium text-slate-500 dark:text-zinc-400">
                                PT Triple One Global
                            </span>
                        </div>
                    </a>

                    <div class="flex items-center gap-2.5" x-data>
                        <flux:button
                            variant="subtle"
                            size="sm"
                            x-on:click="$flux.dark = ! $flux.dark"
                            aria-label="Ubah tema Light/Dark"
                            class="border border-slate-200 dark:border-zinc-700"
                        >
                            <flux:icon name="sun" class="size-4 hidden dark:block text-amber-400" />
                            <flux:icon name="moon" class="size-4 block dark:hidden text-slate-700" />
                        </flux:button>

                        @if (Route::has('login'))
                            @auth
                                <flux:button variant="primary" size="sm" :href="route('dashboard')" icon-trailing="arrow-right">
                                    Buka Dashboard
                                </flux:button>
                            @else
                                <flux:button variant="ghost" size="sm" :href="route('login')">
                                    Masuk
                                </flux:button>
                                @if (Route::has('register'))
                                    <flux:button variant="primary" size="sm" :href="route('register')">
                                        Daftar
                                    </flux:button>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main
                class="relative z-10 mx-auto flex w-full max-w-6xl flex-1 flex-col justify-center px-6 py-14 lg:py-20 gap-14"
                style="padding-top: 56px; padding-bottom: 64px; gap: 56px;"
            >
                {{-- Hero 2 Kolom --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center" style="gap: 48px;">
                    {{-- Left Column: Headline, Deskripsi, CTA, Feature Pills --}}
                    <div class="lg:col-span-6 space-y-6" style="display: flex; flex-direction: column; gap: 24px;">
                        {{-- Version Badge --}}
                        <div>
                            <div class="inline-flex items-center gap-2.5 rounded-full border border-blue-200 dark:border-blue-500/40 bg-blue-50 dark:bg-zinc-900 px-4 py-2 text-xs sm:text-sm font-semibold text-blue-700 dark:text-sky-300">
                                <span class="relative flex shrink-0" style="width: 10px; height: 10px;">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full bg-blue-600 dark:bg-sky-400" style="width: 10px; height: 10px;"></span>
                                </span>
                                <span>Sistem Informasi Eksekutif v2.0</span>
                            </div>
                        </div>

                        {{-- Headline & Description --}}
                        <div class="space-y-4" style="display: flex; flex-direction: column; gap: 16px;">
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-slate-900 dark:text-white leading-tight">
                                Pantau Trafik &amp; Performa
                                <span class="text-blue-600 dark:text-sky-400">SMS A2P</span>
                                Secara Real-Time
                            </h1>

                            <p class="text-base text-slate-600 dark:text-zinc-300 leading-relaxed" style="max-width: 540px;">
                                Dasbor analitik terpusat untuk pimpinan dan tim operasional
                                <strong class="font-semibold text-slate-900 dark:text-white">PT Triple One Global</strong>.
                                Evaluasi volume pengiriman, <em class="text-slate-800 dark:text-zinc-200">Delivery Rate</em>, dan distribusi operator dalam satu tampilan modern.
                            </p>
                        </div>

                        {{-- CTA Buttons --}}
                        <div class="flex flex-wrap items-center gap-3" style="gap: 12px; padding-top: 4px;">
                            @auth
                                <flux:button variant="primary" :href="route('dashboard')" icon="chart-pie" class="px-5 py-2.5 text-sm font-semibold">
                                    Masuk ke Dashboard
                                </flux:button>
                            @else
                                <flux:button variant="primary" :href="route('login')" icon="arrow-right-end-on-rectangle" class="px-5 py-2.5 text-sm font-semibold">
                                    Masuk ke Dashboard
                                </flux:button>
                                @if (Route::has('register'))
                                    <flux:button variant="subtle" :href="route('register')" icon="user-plus" class="px-5 py-2.5 text-sm border border-slate-200 dark:border-zinc-700">
                                        Daftar Akun
                                    </flux:button>
                                @endif
                            @endauth
                        </div>

                        {{-- Feature Pills (Kontras Jelas di Light & Dark Mode) --}}
                        <div class="flex flex-wrap items-center gap-3" style="gap: 12px; padding-top: 4px;">
                            @foreach ([
                                ['icon' => 'bolt', 'color' => 'text-amber-500 dark:text-amber-400', 'label' => 'Agregasi Real-Time'],
                                ['icon' => 'shield-check', 'color' => 'text-emerald-600 dark:text-emerald-400', 'label' => 'Akses Berbasis Role'],
                                ['icon' => 'moon', 'color' => 'text-blue-600 dark:text-sky-400', 'label' => 'Dual Mode Light & Dark'],
                            ] as $feature)
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2 text-xs font-medium text-slate-700 dark:text-zinc-200">
                                    <flux:icon :name="$feature['icon']" class="size-4 shrink-0 {{ $feature['color'] }}" />
                                    <span>{{ $feature['label'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Right Column: Interactive Executive Preview Card --}}
                    @php
                        $dbOperators = collect();
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('sms_traffics')) {
                                $dbOperators = \App\Models\SmsTraffic::query()
                                    ->selectRaw('operator, SUM(total_dikirim) as total')
                                    ->groupBy('operator')
                                    ->orderByDesc('total')
                                    ->get();
                            }
                        } catch (\Throwable) {
                            $dbOperators = collect();
                        }

                        $operatorColors = [
                            'bg-blue-600 dark:bg-blue-500',
                            'bg-blue-500 dark:bg-sky-400',
                            'bg-sky-400 dark:bg-sky-300',
                            'bg-indigo-500 dark:bg-indigo-400',
                            'bg-cyan-500 dark:bg-cyan-400',
                            'bg-teal-500 dark:bg-teal-400',
                        ];

                        if ($dbOperators->isNotEmpty()) {
                            $grandTotal = (int) $dbOperators->sum('total');
                            $topOperators = $dbOperators->values()->map(function ($row, $idx) use ($grandTotal, $operatorColors) {
                                return [
                                    'name' => $row->operator,
                                    'pct' => $grandTotal > 0 ? round(((int) $row->total / $grandTotal) * 100, 1) : 0,
                                    'color' => $operatorColors[$idx % count($operatorColors)],
                                ];
                            });
                        } else {
                            $topOperators = collect([
                                ['name' => 'Telkomsel', 'pct' => 42, 'color' => $operatorColors[0]],
                                ['name' => 'Indosat', 'pct' => 28, 'color' => $operatorColors[1]],
                                ['name' => 'XL Axiata', 'pct' => 19, 'color' => $operatorColors[2]],
                            ]);
                        }

                        $operatorCount = $topOperators->count();
                        $opTextStyle = match (true) {
                            $operatorCount <= 3 => 'font-size: 13px; line-height: 1.35;',
                            $operatorCount <= 5 => 'font-size: 12px; line-height: 1.25;',
                            default => 'font-size: 11px; line-height: 1.2;',
                        };
                        $opGapStyle = match (true) {
                            $operatorCount <= 3 => 'gap: 12px;',
                            $operatorCount <= 5 => 'gap: 8px;',
                            default => 'gap: 6px;',
                        };
                        $opBarHeight = $operatorCount <= 3 ? '8px' : ($operatorCount <= 5 ? '6px' : '5px');
                    @endphp
                    <div
                        class="lg:col-span-6"
                        x-data="{
                            period: '7d',
                            stats: {
                                '7d': {
                                    sent: '128.450',
                                    delta: '+12.4%',
                                    rate: '98.6',
                                    subtitle: 'Volume SMS per hari (7 hari)',
                                    bars: [
                                        { label: 'Sen', height: 48 },
                                        { label: 'Sel', height: 68 },
                                        { label: 'Rab', height: 56 },
                                        { label: 'Kam', height: 84 },
                                        { label: 'Jum', height: 94 },
                                        { label: 'Sab', height: 72 },
                                        { label: 'Min', height: 88 },
                                    ]
                                },
                                '30d': {
                                    sent: '542.900',
                                    delta: '+18.2%',
                                    rate: '99.1',
                                    subtitle: 'Tren volume mingguan (30 hari)',
                                    bars: [
                                        { label: 'M1', height: 64 },
                                        { label: 'M2', height: 76 },
                                        { label: 'M3', height: 84 },
                                        { label: 'M4', height: 96 },
                                    ]
                                }
                            }
                        }"
                    >
                        <div
                            class="rounded-2xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-xl shadow-slate-200/50 dark:shadow-none"
                            style="display: flex; flex-direction: column; gap: 20px;"
                        >
                            {{-- Card Header --}}
                            <div
                                class="flex items-center justify-between gap-3 border-b border-slate-200 dark:border-zinc-800"
                                style="padding-bottom: 16px;"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span class="rounded-full bg-emerald-500 shrink-0" style="width: 8px; height: 8px;"></span>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                        Ringkasan Analitik
                                    </span>
                                </div>

                                <div
                                    class="inline-flex rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-xs font-medium"
                                    style="position: relative; padding: 4px;"
                                >
                                    <div
                                        aria-hidden="true"
                                        class="pointer-events-none"
                                        :style="`position: absolute; top: 4px; bottom: 4px; left: 4px; z-index: 1; border-radius: 6px; width: calc(50% - 4px); background-color: #2563eb; box-shadow: 0 2px 10px rgba(37, 99, 235, 0.35); will-change: transform; transform: translate3d(${period === '30d' ? '100%' : '0%'}, 0, 0); transition: transform 0.42s cubic-bezier(0.32, 0.72, 0, 1);`"
                                    ></div>
                                    <button
                                        type="button"
                                        x-on:click="period = '7d'"
                                        :class="period === '7d' ? 'text-white' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'"
                                        :style="`position: relative; z-index: 2; ${period === '7d' ? 'color: #ffffff;' : ''}`"
                                        class="rounded-md px-3 py-1 transition-colors cursor-pointer"
                                    >
                                        7 Hari
                                    </button>
                                    <button
                                        type="button"
                                        x-on:click="period = '30d'"
                                        :class="period === '30d' ? 'text-white' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'"
                                        :style="`position: relative; z-index: 2; ${period === '30d' ? 'color: #ffffff;' : ''}`"
                                        class="rounded-md px-3 py-1 transition-colors cursor-pointer"
                                    >
                                        30 Hari
                                    </button>
                                </div>
                            </div>

                            {{-- 2-Column KPI Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Total Dikirim --}}
                                <div class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 flex flex-col justify-between gap-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                                            Total Dikirim
                                        </span>
                                        <div class="flex size-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-zinc-800 text-blue-600 dark:text-sky-400">
                                            <flux:icon name="paper-airplane" class="size-4" />
                                        </div>
                                    </div>
                                    <div class="flex items-baseline justify-between gap-2">
                                        <p class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white tabular-nums" x-text="stats[period].sent"></p>
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 dark:border-emerald-500/40 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                            <flux:icon name="arrow-trending-up" class="size-3.5" />
                                            <span x-text="stats[period].delta"></span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Delivery Rate --}}
                                <div class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 flex flex-col justify-between gap-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                                            Delivery Rate
                                        </span>
                                        <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400">
                                            <flux:icon name="check-badge" class="size-4" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-baseline justify-between">
                                            <p class="inline-flex items-baseline text-2xl font-bold tracking-tight text-slate-900 dark:text-white tabular-nums">
                                                <span x-text="stats[period].rate"></span><span class="text-sm font-semibold text-blue-600 dark:text-sky-400" style="margin-left: 4px;">%</span>
                                            </p>
                                            <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">SLA &ge; 95%</span>
                                        </div>
                                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-zinc-800">
                                            <div
                                                class="h-2.5 rounded-full bg-blue-600 dark:bg-sky-400 transition-all duration-500"
                                                :style="`width: ${stats[period].rate}%`"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bottom 2-Column Section --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Trafik Harian --}}
                                <div class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 flex flex-col justify-between gap-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="block text-xs font-semibold text-slate-900 dark:text-white">Trafik Harian</span>
                                            <span class="block text-[11px] text-slate-500 dark:text-zinc-400" x-text="stats[period].subtitle"></span>
                                        </div>
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 dark:border-emerald-500/40 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 shrink-0">
                                            <span class="rounded-full bg-emerald-500 shrink-0" style="width: 6px; height: 6px;"></span>
                                            Stabil
                                        </span>
                                    </div>

                                    {{-- Upright Bar Chart + Horizontal Labels --}}
                                    <div class="pt-2" style="display: flex; align-items: flex-end; justify-content: space-between; gap: 8px;">
                                        <template x-for="(item, idx) in stats[period].bars" :key="period + '-' + idx">
                                            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 0;">
                                                <div style="height: 80px; width: 100%; display: flex; align-items: flex-end;">
                                                    <div
                                                        class="w-full rounded-t-md bg-blue-600 dark:bg-sky-400 transition-all duration-500"
                                                        :style="`height: ${item.height}%; min-height: 6px;`"
                                                    ></div>
                                                </div>
                                                <span
                                                    class="font-medium text-slate-500 dark:text-zinc-400"
                                                    style="font-size: 11px; line-height: 1;"
                                                    x-text="item.label"
                                                ></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Top Operator (Ukuran Font & Jarak Otomatis Mengikuti Jumlah Operator) --}}
                                <div class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 flex flex-col justify-between gap-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-slate-900 dark:text-white">Top Operator</span>
                                        <span class="rounded-full border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:text-zinc-300">
                                            {{ $operatorCount }} Operator
                                        </span>
                                    </div>

                                    <div style="display: flex; flex-direction: column; {{ $opGapStyle }}">
                                        @foreach ($topOperators as $op)
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                <div class="flex items-center justify-between gap-2" style="{{ $opTextStyle }}">
                                                    <span class="truncate font-medium text-slate-700 dark:text-zinc-300">{{ $op['name'] }}</span>
                                                    <span class="shrink-0 font-semibold text-slate-900 dark:text-white tabular-nums">{{ $op['pct'] }}%</span>
                                                </div>
                                                <div class="w-full rounded-full bg-slate-200 dark:bg-zinc-800 overflow-hidden" style="height: {{ $opBarHeight }};">
                                                    <div class="rounded-full {{ $op['color'] }}" style="height: {{ $opBarHeight }}; width: {{ min(100, max(2, $op['pct'])) }}%;"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3 Kartu Fitur Utama --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach ([
                        [
                            'icon' => 'chart-bar',
                            'iconBg' => 'bg-blue-100 dark:bg-zinc-800',
                            'iconColor' => 'text-blue-600 dark:text-sky-400',
                            'title' => 'KPI & Tren Interaktif',
                            'desc' => 'Filter periode cepat, rentang tanggal kustom, dan perbandingan delta tren otomatis.',
                        ],
                        [
                            'icon' => 'signal',
                            'iconBg' => 'bg-emerald-100 dark:bg-zinc-800',
                            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
                            'title' => 'Distribusi Operator Akurat',
                            'desc' => 'Pantau pangsa trafik Telkomsel, Indosat, XL Axiata, dan operator lainnya secara visual.',
                        ],
                        [
                            'icon' => 'shield-check',
                            'iconBg' => 'bg-amber-100 dark:bg-zinc-800',
                            'iconColor' => 'text-amber-600 dark:text-amber-400',
                            'title' => 'Kontrol Akses Berbasis Role',
                            'desc' => 'Pemisahan hak akses aman untuk Pengguna Umum, Eksekutif, dan Admin IT.',
                        ],
                    ] as $card)
                        <div class="rounded-2xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 transition-all duration-200 hover:border-blue-300 dark:hover:border-zinc-700 hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $card['iconBg'] }}">
                                    <flux:icon :name="$card['icon']" class="size-5 {{ $card['iconColor'] }}" />
                                </div>
                                <div class="space-y-1.5">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $card['title'] }}
                                    </h3>
                                    <p class="text-xs leading-relaxed text-slate-600 dark:text-zinc-400">
                                        {{ $card['desc'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </main>

            {{-- Footer --}}
            <footer class="relative z-10 border-t border-slate-200 dark:border-zinc-800 bg-white/60 dark:bg-zinc-950/60 py-5 text-center text-xs text-slate-500 dark:text-zinc-400">
                &copy; {{ date('Y') }} PT Triple One Global &mdash; Sistem Informasi Eksekutif SMS A2P
            </footer>
        </div>

        @fluxScripts
    </body>
</html>

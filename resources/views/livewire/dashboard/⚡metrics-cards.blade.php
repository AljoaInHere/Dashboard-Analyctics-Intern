<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

    {{-- KARTU 1: Total SMS Terkirim --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-royal-700 to-navy-900 p-6 shadow-lg shadow-royal-700/20 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-sky-300">Total SMS Terkirim</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight">
                    {{ number_format($totalSent) }}
                </h3>
                <p class="mt-1 text-xs text-sky-200">Hari ini</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10">
                <flux:icon name="paper-airplane" class="h-6 w-6 text-sky-300" />
            </div>
        </div>
        {{-- Aksen dekoratif lingkaran transparan --}}
        <div class="pointer-events-none absolute -right-6 -bottom-6 h-28 w-28 rounded-full bg-sky-400/10"></div>
    </div>

    {{-- KARTU 2: Delivery Rate --}}
    <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-lg shadow-slate-200 border border-blue-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-navy-900/60">Delivery Rate</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-navy-900">
                    {{ $deliveryRate }}<span class="text-lg text-royal-600">%</span>
                </h3>
                <p class="mt-1 text-xs font-medium text-emerald-600">
                    ● Sukses terkirim
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface-blue">
                <flux:icon name="chart-bar" class="h-6 w-6 text-royal-600" />
            </div>
        </div>

        {{-- Progress bar mini --}}
        <div class="mt-4 h-2 w-full rounded-full bg-blue-100">
            <div
                class="h-2 rounded-full bg-gradient-to-r from-royal-600 to-sky-400 transition-all duration-700"
                style="width: {{ $deliveryRate }}%"
            ></div>
        </div>
    </div>

    {{-- KARTU 3: Total SMS Gagal --}}
    <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-lg shadow-slate-200 border border-blue-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-navy-900/60">SMS Gagal / Failed</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-navy-900">
                    {{ number_format($totalFailed) }}
                </h3>
                <p class="mt-1 text-xs font-medium text-rose-500">
                    ● Perlu ditinjau
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slateblue-500/10">
                <flux:icon name="exclamation-triangle" class="h-6 w-6 text-slateblue-500" />
            </div>
        </div>
    </div>

</div>
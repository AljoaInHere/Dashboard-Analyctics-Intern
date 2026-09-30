<div class="rounded-2xl bg-white p-6 shadow-lg shadow-slate-200 border border-blue-100">

    <div class="mb-4">
        <h3 class="text-base font-semibold text-navy-900">Distribusi per Operator</h3>
        <p class="text-xs text-slate-500">Volume SMS terkirim berdasarkan operator</p>
    </div>

    <div wire:ignore
         x-data="operatorChart(@js($operatorLabels), @js($operatorData))"
         x-init="init()"
         class="relative mx-auto h-64 w-full max-w-[260px]">
        <canvas x-ref="canvas"></canvas>
    </div>

    {{-- Legend custom di bawah chart, biar rapi & konsisten dengan tema --}}
    <div class="mt-5 space-y-2">
        @foreach ($operatorLabels as $i => $label)
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-2 text-slate-600">
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ ['#1d4ed8','#3b82f6','#38bdf8','#7dd3fc','#5b6fd8'][$i] }}"></span>
                    {{ $label }}
                </span>
                <span class="font-medium text-navy-900">{{ number_format($operatorData[$i]) }}</span>
            </div>
        @endforeach
    </div>

</div>

@script
<script>
    Alpine.data('operatorChart', (labels, data) => ({
        chartInstance: null,

        init() {
            const ctx = this.$refs.canvas.getContext('2d');

            // Palet biru bertingkat: dari gelap (royal) ke terang (sky/slate)
            const bluePalette = ['#1d4ed8', '#3b82f6', '#38bdf8', '#7dd3fc', '#5b6fd8'];

            this.chartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: bluePalette,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 10,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false }, // pakai legend custom di Blade
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
                                    const pct = ((value / total) * 100).toFixed(1);
                                    return ` ${context.label}: ${value.toLocaleString()} (${pct}%)`;
                                },
                            },
                        },
                    },
                    animation: { animateRotate: true, animateScale: true, duration: 900 },
                },
            });
        },
    }));
</script>
@endscript
<div class="rounded-2xl bg-white p-6 shadow-lg shadow-slate-200 border border-blue-100 h-full flex flex-col justify-between">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-base font-semibold text-navy-900">Tren Trafik SMS Mingguan</h3>
            <p class="text-xs text-slate-500">Perbandingan SMS terkirim vs gagal (7 hari terakhir)</p>
        </div>
        <!-- Legend dibuat badge rapi -->
        <div class="flex items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-medium border border-blue-200">
                <span class="h-2 w-2 rounded-full bg-blue-600"></span> Terkirim
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-50 text-sky-700 font-medium border border-sky-200">
                <span class="h-2 w-2 rounded-full bg-sky-400"></span> Gagal
            </span>
        </div>
    </div>

    {{-- Canvas Container --}}
    <div wire:ignore
         x-data="trendChart(@js($labels), @js($sentData), @js($failedData))"
         x-init="init()"
         class="relative h-72 w-full mt-auto">
        <canvas x-ref="canvas"></canvas>
    </div>

</div>

@script
<script>
    Alpine.data('trendChart', (labels, sentData, failedData) => ({
        chartInstance: null,

        init() {
            const ctx = this.$refs.canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 280);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'SMS Terkirim',
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
                            borderColor: '#38bdf8',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.4,
                            pointBackgroundColor: '#38bdf8',
                            pointRadius: 3,
                            pointHoverRadius: 5,
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
                        x: { grid: { display: false }, ticks: { color: '#64748b' } },
                        y: {
                            grid: { color: '#eef4ff' },
                            ticks: { color: '#64748b' },
                            beginAtZero: true,
                        },
                    },
                    animation: { duration: 800, easing: 'easeOutQuart' },
                },
            });
        },

        updateChart(newSent, newFailed) {
            this.chartInstance.data.datasets[0].data = newSent;
            this.chartInstance.data.datasets[1].data = newFailed;
            this.chartInstance.update();
        },
    }));
</script>
@endscript
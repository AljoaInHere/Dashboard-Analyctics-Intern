<div class="p-6 space-y-6">
    <!-- Kotak Kartu Angka (Metrics) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm font-medium">Total Trafik Bulan Ini</h3>
            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">
                {{ number_format($totalBulanIni) }} SMS
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-gray-500 text-sm font-medium">Rasio Keberhasilan (Delivery Rate)</h3>
            <p class="text-3xl font-bold text-green-600 mt-2">
                {{ $deliveryRate }}%
            </p>
        </div>
    </div>

    <!-- Area Grafik -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-4">Grafik Tren 7 Hari Terakhir</h3>
        <!-- Data dari backend ($labelsTren & $dataTren) siap disambungkan ke Chart.js oleh temanmu -->
    </div>
</div>
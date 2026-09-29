<x-layouts::app>
    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Judul Dashboard -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Sistem Informasi Eksekutif - Analitik SMS A2P
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ringkasan trafik pengiriman SMS A2P dan performa operator.
            </p>
        </div>

        <!-- Komponen Livewire Utama -->
        @livewire('dashboard-analytics')
    </div>
</x-layouts::app>
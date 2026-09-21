<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SmsTraffic;
use Carbon\Carbon;

class DashboardAnalytics extends Component
{
    public function render()
    {
        // 1. QUERY UNTUK KARTU METRIK (Atas)
        $totalBulanIni = SmsTraffic::whereMonth('tanggal', Carbon::now()->month)->sum('total_dikirim');
        $totalSukses = SmsTraffic::whereMonth('tanggal', Carbon::now()->month)->sum('total_sukses');
        $deliveryRate = $totalBulanIni > 0 ? round(($totalSukses / $totalBulanIni) * 100, 2) : 0;

        // 2. QUERY UNTUK GRAFIK GARIS (Line Chart - Tren 7 Hari Terakhir)
        $trenMingguan = SmsTraffic::selectRaw('tanggal, SUM(total_dikirim) as total')
            ->where('tanggal', '>=', Carbon::now()->subDays(7))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // 3. QUERY UNTUK GRAFIK RASIO OPERATOR (Doughnut Chart)
        $rasioOperator = SmsTraffic::selectRaw('operator, SUM(total_dikirim) as total')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->groupBy('operator')
            ->get();

        // Kirim data hasil query di atas ke tampilan frontend (Blade)
        return view('livewire.dashboard-analytics', [
            'totalBulanIni' => $totalBulanIni,
            'deliveryRate' => $deliveryRate,
            'labelsTren' => $trenMingguan->pluck('tanggal'),
            'dataTren' => $trenMingguan->pluck('total'),
            'labelsOperator' => $rasioOperator->pluck('operator'),
            'dataOperator' => $rasioOperator->pluck('total'),
        ]);
    }
}
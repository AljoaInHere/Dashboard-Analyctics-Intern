<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class TrendChart extends Component
{
    public array $labels = [];
    public array $sentData = [];
    public array $failedData = [];

    public function mount(): void
    {
        // Data dummy 7 hari terakhir.
        // Nanti tim backend tinggal ganti dengan query per hari, misal:
        // SmsLog::whereBetween('created_at', [...])->groupBy('date')->...
        $this->labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $this->sentData   = [1200, 1900, 1700, 2100, 2400, 1500, 1800];
        $this->failedData = [30, 45, 25, 60, 40, 20, 35];
    }

    public function render()
    {
        return view('livewire.dashboard.⚡trend-chart');
    }
}

<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class MetricsCards extends Component
{
    // Properti data dummy
    public int $totalSent = 12450;
    public int $totalFailed = 187;
    public float $deliveryRate = 0;

    public function mount(): void
    {
        // Hitung delivery rate otomatis dari data dummy
        $totalAttempt = $this->totalSent + $this->totalFailed;
        $this->deliveryRate = $totalAttempt > 0
            ? round(($this->totalSent / $totalAttempt) * 100, 1)
            : 0;
    }

    public function render()
    {
        return view('livewire.dashboard.⚡metrics-cards');
    }
}
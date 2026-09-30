<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class OperatorChart extends Component
{
    public array $operatorLabels = [];
    public array $operatorData = [];

    public function mount(): void
    {
        // Dummy distribusi pengiriman per operator telekomunikasi.
        // Nanti diganti query, misal: SmsLog::select('operator')->groupBy('operator')->count()
        $this->operatorLabels = ['Telkomsel', 'Indosat', 'XL Axiata', 'Tri', 'Smartfren'];
        $this->operatorData   = [4200, 3100, 2500, 1600, 900];
    }

    public function render()
    {
        return view('livewire.dashboard.⚡operator-chart');
    }
}

<?php

namespace App\Livewire;

use App\Models\SmsTraffic;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardAnalytics extends Component
{
    use WithFileUploads, WithPagination;

    public string $preset = 'this_month';

    public string $startDate = '';

    public string $endDate = '';

    public string $operator = 'all';

    public string $sortBy = 'tanggal';

    public string $sortDirection = 'desc';

    // Modal & Form Input Manual
    public bool $showInputModal = false;

    public string $inputTanggal = '';

    public string $inputOperator = '';

    public int|string $inputTotalDikirim = '';

    public int|string $inputTotalSukses = '';

    public int|string $inputTotalGagal = '';

    public bool $confirmOverwrite = false;

    // Modal & Form Import CSV
    public bool $showImportModal = false;

    public $csvFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $importPreview = [];

    public bool $importOverwriteDuplicates = false;

    /** @var array{imported: int, updated: int, skipped: int, errors: array<int, string>}|null */
    public ?array $importSummary = null;

    public function mount(): void
    {
        $this->applyPresetDates('this_month');
        $this->inputTanggal = Carbon::now()->toDateString();

        $hasCurrentMonthData = SmsTraffic::query()
            ->betweenDates($this->startDate, $this->endDate)
            ->exists();

        if (! $hasCurrentMonthData && SmsTraffic::query()->exists()) {
            $this->applyPresetDates('all');
        }
    }

    public function setPreset(string $preset): void
    {
        $order = ['today' => 0, '7_days' => 1, '30_days' => 2, 'this_month' => 3, 'all' => 4];
        $direction = ($order[$preset] ?? 0) >= ($order[$this->preset] ?? 0) ? 'next' : 'prev';

        $this->applyPresetDates($preset);
        $this->resetPage();
        $this->dispatch('period-slid', direction: $direction);
    }

    public function shiftPeriod(int $direction): void
    {
        $step = $direction >= 0 ? 1 : -1;
        [$start, $end] = $this->resolvedDateRange();

        $startCarbon = Carbon::parse($start);
        $endCarbon = Carbon::parse($end);
        $daysSpan = $startCarbon->diffInDays($endCarbon) + 1;

        $this->startDate = $startCarbon->copy()->addDays($step * $daysSpan)->toDateString();
        $this->endDate = $endCarbon->copy()->addDays($step * $daysSpan)->toDateString();
        $this->preset = 'custom';
        $this->resetPage();
        $this->dispatch('period-slid', direction: $step > 0 ? 'next' : 'prev');
    }

    public function resetFilters(): void
    {
        $this->operator = 'all';
        $this->applyPresetDates('all');
        $this->resetPage();
    }

    public function selectDate(string $date): void
    {
        if (! strtotime($date)) {
            return;
        }

        $normalized = Carbon::parse($date)->toDateString();

        if ($this->startDate === $normalized && $this->endDate === $normalized && $this->preset === 'custom') {
            $this->applyPresetDates('all');
        } else {
            $this->startDate = $normalized;
            $this->endDate = $normalized;
            $this->preset = 'custom';
        }

        $this->resetPage();
    }

    public function selectOperator(string $operator): void
    {
        $trimmed = trim($operator);

        if ($trimmed === '') {
            return;
        }

        $this->operator = $this->operator === $trimmed ? 'all' : $trimmed;
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->preset = 'custom';
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->preset = 'custom';
        $this->resetPage();
    }

    public function updatedOperator(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        $allowed = ['tanggal', 'operator', 'total_dikirim', 'total_sukses', 'total_gagal'];

        if (! in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    public function setSort(string $column, string $direction = 'desc'): void
    {
        $allowed = ['tanggal', 'operator', 'total_dikirim', 'total_sukses', 'total_gagal'];

        if (! in_array($column, $allowed, true)) {
            return;
        }

        $this->sortBy = $column;
        $this->sortDirection = $direction === 'asc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    public function openInputModal(): void
    {
        $this->authorizeWriteAccess();
        $this->resetInputForm();
        $this->showInputModal = true;
    }

    public function saveTraffic(bool $forceOverwrite = false): void
    {
        $this->authorizeWriteAccess();

        $validated = $this->validate([
            'inputTanggal' => ['required', 'date'],
            'inputOperator' => ['required', 'string', 'max:100'],
            'inputTotalDikirim' => ['required', 'integer', 'min:0'],
            'inputTotalSukses' => ['required', 'integer', 'min:0'],
            'inputTotalGagal' => ['required', 'integer', 'min:0'],
        ], [], [
            'inputTanggal' => 'Tanggal',
            'inputOperator' => 'Operator',
            'inputTotalDikirim' => 'Total Dikirim',
            'inputTotalSukses' => 'Total Sukses',
            'inputTotalGagal' => 'Total Gagal',
        ]);

        $dikirim = (int) $validated['inputTotalDikirim'];
        $sukses = (int) $validated['inputTotalSukses'];
        $gagal = (int) $validated['inputTotalGagal'];

        if ($sukses + $gagal !== $dikirim) {
            $this->addError('inputTotalDikirim', 'Total Sukses + Total Gagal harus sama dengan Total Dikirim.');

            return;
        }

        $existing = SmsTraffic::query()
            ->whereDate('tanggal', $validated['inputTanggal'])
            ->where('operator', trim($validated['inputOperator']))
            ->first();

        if ($existing && ! $forceOverwrite) {
            $this->confirmOverwrite = true;

            return;
        }

        SmsTraffic::updateOrCreate(
            [
                'tanggal' => $validated['inputTanggal'],
                'operator' => trim($validated['inputOperator']),
            ],
            [
                'total_dikirim' => $dikirim,
                'total_sukses' => $sukses,
                'total_gagal' => $gagal,
            ]
        );

        $this->showInputModal = false;
        $this->resetInputForm();

        if (class_exists(Flux::class)) {
            Flux::toast(
                heading: 'Data tersimpan',
                text: 'Data trafik SMS berhasil disimpan.',
                variant: 'success',
            );
        }
    }

    public function openImportModal(): void
    {
        $this->authorizeWriteAccess();
        $this->resetImportForm();
        $this->showImportModal = true;
    }

    public function updatedCsvFile(): void
    {
        $this->authorizeWriteAccess();
        $this->importPreview = [];
        $this->importSummary = null;

        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            $this->addError('csvFile', 'Gagal membaca berkas CSV.');

            return;
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            $this->addError('csvFile', 'Berkas CSV kosong.');

            return;
        }

        $normalizedHeader = array_map(fn ($col) => strtolower(trim((string) $col)), $header);
        $required = ['tanggal', 'operator', 'total_dikirim', 'total_sukses', 'total_gagal'];

        foreach ($required as $col) {
            if (! in_array($col, $normalizedHeader, true)) {
                fclose($handle);
                $this->addError('csvFile', "Kolom wajib '{$col}' tidak ditemukan di header CSV.");

                return;
            }
        }

        $indexMap = array_flip($normalizedHeader);
        $preview = [];

        while (($row = fgetcsv($handle)) !== false && count($preview) < 100) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $tanggal = trim((string) ($row[$indexMap['tanggal']] ?? ''));
            $operator = trim((string) ($row[$indexMap['operator']] ?? ''));
            $dikirim = (int) ($row[$indexMap['total_dikirim']] ?? -1);
            $sukses = (int) ($row[$indexMap['total_sukses']] ?? -1);
            $gagal = (int) ($row[$indexMap['total_gagal']] ?? -1);

            $error = null;
            if (! strtotime($tanggal) || $operator === '') {
                $error = 'Tanggal atau operator tidak valid';
            } elseif ($dikirim < 0 || $sukses < 0 || $gagal < 0) {
                $error = 'Angka tidak boleh negatif';
            } elseif ($sukses + $gagal !== $dikirim) {
                $error = 'Sukses + Gagal != Dikirim';
            }

            $isDuplicate = $error === null && SmsTraffic::query()
                ->whereDate('tanggal', Carbon::parse($tanggal)->toDateString())
                ->where('operator', $operator)
                ->exists();

            $preview[] = [
                'tanggal' => $error === null ? Carbon::parse($tanggal)->toDateString() : $tanggal,
                'operator' => $operator,
                'total_dikirim' => $dikirim,
                'total_sukses' => $sukses,
                'total_gagal' => $gagal,
                'duplicate' => $isDuplicate,
                'error' => $error,
            ];
        }

        fclose($handle);
        $this->importPreview = $preview;
    }

    public function confirmImport(): void
    {
        $this->authorizeWriteAccess();

        if (empty($this->importPreview)) {
            $this->addError('csvFile', 'Tidak ada baris data untuk di-import.');

            return;
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($this->importPreview as $i => $row) {
            if ($row['error'] !== null) {
                $errors[] = 'Baris '.($i + 1).': '.$row['error'];
                $skipped++;

                continue;
            }

            $existing = SmsTraffic::query()
                ->whereDate('tanggal', $row['tanggal'])
                ->where('operator', $row['operator'])
                ->first();

            if ($existing && ! $this->importOverwriteDuplicates) {
                $skipped++;

                continue;
            }

            if ($existing) {
                $existing->update([
                    'total_dikirim' => $row['total_dikirim'],
                    'total_sukses' => $row['total_sukses'],
                    'total_gagal' => $row['total_gagal'],
                ]);
                $updated++;
            } else {
                SmsTraffic::create([
                    'tanggal' => $row['tanggal'],
                    'operator' => $row['operator'],
                    'total_dikirim' => $row['total_dikirim'],
                    'total_sukses' => $row['total_sukses'],
                    'total_gagal' => $row['total_gagal'],
                ]);
                $imported++;
            }
        }

        $this->importSummary = [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
        $this->importPreview = [];
        $this->csvFile = null;
    }

    public function exportCsv(): StreamedResponse
    {
        $this->authorizeWriteAccess();

        [$start, $end] = $this->resolvedDateRange();
        $rows = SmsTraffic::query()
            ->betweenDates($start, $end)
            ->forOperator($this->operator)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();

        $filename = 'sms-traffic-'.$start.'-to-'.$end.'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Operator', 'Total Dikirim', 'Total Sukses', 'Total Gagal', 'Delivery Rate (%)']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->tanggal->format('Y-m-d'),
                    $row->operator,
                    $row->total_dikirim,
                    $row->total_sukses,
                    $row->total_gagal,
                    $row->delivery_rate,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function authorizeWriteAccess(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['eksekutif', 'admin-it']),
            403,
            'Anda tidak memiliki izin untuk melakukan aksi ini.'
        );
    }

    private function resetInputForm(): void
    {
        $this->inputTanggal = Carbon::now()->toDateString();
        $this->inputOperator = '';
        $this->inputTotalDikirim = '';
        $this->inputTotalSukses = '';
        $this->inputTotalGagal = '';
        $this->confirmOverwrite = false;
        $this->resetValidation();
    }

    private function resetImportForm(): void
    {
        $this->csvFile = null;
        $this->importPreview = [];
        $this->importOverwriteDuplicates = false;
        $this->importSummary = null;
        $this->resetValidation();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolvedDateRange(): array
    {
        $start = $this->startDate ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $this->endDate ?: Carbon::now()->toDateString();

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    private function applyPresetDates(string $preset): void
    {
        $now = Carbon::now();
        $this->preset = $preset;

        match ($preset) {
            'today' => [
                $this->startDate = $now->toDateString(),
                $this->endDate = $now->toDateString(),
            ],
            '7_days' => [
                $this->startDate = $now->copy()->subDays(6)->toDateString(),
                $this->endDate = $now->toDateString(),
            ],
            '30_days' => [
                $this->startDate = $now->copy()->subDays(29)->toDateString(),
                $this->endDate = $now->toDateString(),
            ],
            'all' => [
                $this->startDate = ($min = SmsTraffic::query()->min('tanggal'))
                    ? Carbon::parse($min)->toDateString()
                    : $now->copy()->startOfMonth()->toDateString(),
                $this->endDate = ($max = SmsTraffic::query()->max('tanggal'))
                    ? Carbon::parse($max)->toDateString()
                    : $now->toDateString(),
            ],
            default => [
                $this->preset = 'this_month',
                $this->startDate = $now->copy()->startOfMonth()->toDateString(),
                $this->endDate = $now->toDateString(),
            ],
        };
    }

    private function calculatePercentageDelta(float|int $current, float|int $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function render()
    {
        [$start, $end] = $this->resolvedDateRange();

        // 1. QUERY KARTU METRIK (Periode Terpilih)
        $currentTotals = SmsTraffic::query()
            ->betweenDates($start, $end)
            ->forOperator($this->operator)
            ->selectRaw('COALESCE(SUM(total_dikirim), 0) as total_dikirim, COALESCE(SUM(total_sukses), 0) as total_sukses, COALESCE(SUM(total_gagal), 0) as total_gagal')
            ->first();

        $totalBulanIni = (int) ($currentTotals->total_dikirim ?? 0);
        $totalSukses = (int) ($currentTotals->total_sukses ?? 0);
        $totalGagal = (int) ($currentTotals->total_gagal ?? 0);
        $deliveryRate = $totalBulanIni > 0 ? round(($totalSukses / $totalBulanIni) * 100, 2) : 0;

        // 1b. QUERY PERIODE SEBELUMNYA UNTUK DELTA TREN
        $startCarbon = Carbon::parse($start);
        $endCarbon = Carbon::parse($end);
        $daysSpan = $startCarbon->diffInDays($endCarbon) + 1;
        $prevEnd = $startCarbon->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($daysSpan - 1);

        $previousTotals = SmsTraffic::query()
            ->betweenDates($prevStart->toDateString(), $prevEnd->toDateString())
            ->forOperator($this->operator)
            ->selectRaw('COALESCE(SUM(total_dikirim), 0) as total_dikirim, COALESCE(SUM(total_sukses), 0) as total_sukses, COALESCE(SUM(total_gagal), 0) as total_gagal')
            ->first();

        $prevSent = (int) ($previousTotals->total_dikirim ?? 0);
        $prevSuccess = (int) ($previousTotals->total_sukses ?? 0);
        $prevFailed = (int) ($previousTotals->total_gagal ?? 0);
        $prevRate = $prevSent > 0 ? round(($prevSuccess / $prevSent) * 100, 2) : 0;

        $sentDelta = $this->calculatePercentageDelta($totalBulanIni, $prevSent);
        $failedDelta = $this->calculatePercentageDelta($totalGagal, $prevFailed);
        $rateDelta = $prevSent > 0 ? round($deliveryRate - $prevRate, 2) : null;

        // 2. QUERY GRAFIK GARIS (Tren Periode Terpilih)
        $trenMingguan = SmsTraffic::query()
            ->betweenDates($start, $end)
            ->forOperator($this->operator)
            ->selectRaw('tanggal, SUM(total_dikirim) as total, SUM(total_gagal) as gagal')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // 3. QUERY GRAFIK RASIO OPERATOR (Periode Terpilih)
        $rasioOperator = SmsTraffic::query()
            ->betweenDates($start, $end)
            ->forOperator($this->operator)
            ->selectRaw('operator, SUM(total_dikirim) as total')
            ->groupBy('operator')
            ->orderByDesc('total')
            ->get();

        // 4. DAFTAR OPERATOR & TABEL RINCIAN HARIAN
        $availableOperators = SmsTraffic::query()
            ->select('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator');

        $records = SmsTraffic::query()
            ->betweenDates($start, $end)
            ->forOperator($this->operator)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        $labelsTren = $trenMingguan->map(fn ($row) => Carbon::parse($row->tanggal)->format('d M'));
        $dataTren = $trenMingguan->pluck('total')->map(fn ($val) => (int) $val);
        $failedTren = $trenMingguan->pluck('gagal')->map(fn ($val) => (int) $val);
        $labelsOperator = $rasioOperator->pluck('operator');
        $dataOperator = $rasioOperator->pluck('total')->map(fn ($val) => (int) $val);

        $this->dispatch('charts-updated', [
            'trendLabels' => $labelsTren->values()->all(),
            'trendSent' => $dataTren->values()->all(),
            'trendFailed' => $failedTren->values()->all(),
            'operatorLabels' => $labelsOperator->values()->all(),
            'operatorData' => $dataOperator->values()->all(),
        ]);

        return view('livewire.dashboard-analytics', [
            'totalBulanIni' => $totalBulanIni,
            'totalSukses' => $totalSukses,
            'totalGagal' => $totalGagal,
            'deliveryRate' => $deliveryRate,
            'sentDelta' => $sentDelta,
            'rateDelta' => $rateDelta,
            'failedDelta' => $failedDelta,
            'labelsTren' => $labelsTren,
            'dataTren' => $dataTren,
            'failedTren' => $failedTren,
            'labelsOperator' => $labelsOperator,
            'dataOperator' => $dataOperator,
            'availableOperators' => $availableOperators,
            'records' => $records,
            'canWrite' => auth()->user()?->hasAnyRole(['eksekutif', 'admin-it']) ?? false,
        ]);
    }
}

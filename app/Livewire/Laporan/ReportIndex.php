<?php

declare(strict_types=1);

namespace App\Livewire\Laporan;

use App\Domains\Laporan\Services\ReportService;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan bawaan sekolah (FR-1001) + ekspor CSV (FR-1002).
 */
class ReportIndex extends Component
{
    public string $type = 'siswa';

    public string $q = '';

    public string $status = '';

    public string $dari = '';

    public string $sampai = '';

    public ?int $semesterId = null;

    /** @var array{columns: array<int,string>, rows: Collection<int,array<int,mixed>>}|null */
    public ?array $report = null;

    public function mount(): void
    {
        $this->authorize('laporan.view');
    }

    public function generate(ReportService $service): void
    {
        $this->authorize('laporan.view');

        $this->report = $service->generate($this->type, $this->filters());
        session()->flash('ok', count($this->report['rows']).' baris ditampilkan.');
    }

    public function export(ReportService $service): StreamedResponse
    {
        $this->authorize('laporan.export');

        $report = $service->generate($this->type, $this->filters());
        $csv = $service->toCsv($report);
        $filename = 'laporan-'.$this->type.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /** @return array<string,mixed> */
    private function filters(): array
    {
        return [
            'q' => $this->q,
            'status' => $this->status,
            'dari' => $this->dari,
            'sampai' => $this->sampai,
            'semester_id' => $this->semesterId,
        ];
    }

    public function render(): View
    {
        return view('livewire.laporan.report-index', [
            'types' => ReportService::TYPES,
            'semesters' => Semester::orderByDesc('id')->limit(10)->get(['id', 'nama']),
        ])->layout('layouts.app', ['title' => 'Laporan']);
    }
}

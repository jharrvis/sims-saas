<?php

declare(strict_types=1);

namespace App\Domains\Kesiswaan\Http\Controllers;

use App\Domains\Kesiswaan\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Rombel;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ekspor daftar siswa (Excel + PDF) mengikuti filter aktif di daftar siswa:
 * pencarian, status, rombel — atau daftar id terpilih (bulk action).
 */
class StudentExportController extends Controller
{
    public function excel(Request $request): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request);
        $students = $this->filteredQuery($filters);

        return Excel::download(new StudentsExport($students), 'daftar-siswa-'.now()->format('Ymd-His').'.xlsx');
    }

    public function pdf(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $students = $this->filteredQuery($filters);

        $rombel = ! empty($filters['rombel_id'])
            ? Rombel::find($filters['rombel_id'])
            : null;

        $pdf = Pdf::loadView('pdf.siswa-daftar', [
            'sekolah' => app(TenantContext::class)->school(),
            'students' => $students,
            'filters' => $filters,
            'rombel' => $rombel,
            'tahunAjaran' => AcademicYear::where('aktif', true)->first(),
            'logoAbs' => $this->logoAbs(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('daftar-siswa-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * @return array{search: ?string, status: ?string, rombel_id: ?int, ids: list<int>}
     */
    private function validatedFilters(Request $request): array
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'string', 'max:20'],
            'rombel_id' => ['nullable', 'integer'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer'],
        ]);

        return [
            'search' => $data['search'] ?? null,
            'status' => $data['status'] ?? null,
            'rombel_id' => isset($data['rombel_id']) ? (int) $data['rombel_id'] : null,
            'ids' => array_map('intval', $data['ids'] ?? []),
        ];
    }

    /**
     * Query yang sama dengan daftar siswa (StudentIndex): pencarian + filter
     * status/rombel, rombel aktif tahun ajaran berjalan di-eager-load.
     *
     * @param  array{search: ?string, status: ?string, rombel_id: ?int, ids: list<int>}  $filters
     * @return Collection<int, Student>
     */
    private function filteredQuery(array $filters): Collection
    {
        $tahunAktifId = AcademicYear::where('aktif', true)->value('id');

        return Student::query()
            ->with(['rombels' => function ($q) use ($tahunAktifId): void {
                $q->wherePivot('status', 'aktif');
                if ($tahunAktifId) {
                    $q->wherePivot('academic_year_id', $tahunAktifId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }])
            ->when(! empty($filters['ids']), fn ($q) => $q->whereIn('id', $filters['ids']))
            ->when(! empty($filters['search']), fn ($q) => $q->where(function ($w) use ($filters): void {
                $w->where('nama', 'like', '%'.$filters['search'].'%')
                    ->orWhere('nis', 'like', '%'.$filters['search'].'%')
                    ->orWhere('nisn', 'like', '%'.$filters['search'].'%');
            }))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['rombel_id']), function ($q) use ($filters, $tahunAktifId): void {
                // wherePivot() tidak boleh di dalam whereHas (menghasilkan
                // SQL salah `pivot = status`); pakai kolom pivot qualified.
                $q->whereHas('rombels', function ($w) use ($filters, $tahunAktifId): void {
                    $w->where('class_student.status', 'aktif');
                    if ($tahunAktifId) {
                        $w->where('class_student.academic_year_id', $tahunAktifId);
                    }
                    $w->where('rombels.id', (int) $filters['rombel_id']);
                });
            })
            ->orderBy('nama')
            ->get();
    }

    /**
     * Path absolut logo sekolah untuk dompdf (null bila tidak ada).
     */
    private function logoAbs(): ?string
    {
        $school = app(TenantContext::class)->school();
        $path = $school?->logo_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Laporan\Services;

use App\Models\Employee;
use App\Models\Rombel;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Laporan bawaan sekolah (FR-1001): siswa, guru, kelas, mutasi,
 * absensi, nilai. Ekspor CSV (FR-1002) ditangani via toCsv().
 */
class ReportService
{
    public const TYPES = [
        'siswa' => 'Data Siswa',
        'guru' => 'Data Guru & Pegawai',
        'kelas' => 'Data Kelas',
        'mutasi' => 'Mutasi Siswa',
        'absensi' => 'Rekap Absensi',
        'nilai' => 'Rekap Nilai',
    ];

    /**
     * @param  array<string,mixed>  $filters
     * @return array<string,mixed>
     */
    public function generate(string $type, array $filters = []): array
    {
        return match ($type) {
            'siswa' => $this->siswa($filters),
            'guru' => $this->guru($filters),
            'kelas' => $this->kelas($filters),
            'mutasi' => $this->mutasi($filters),
            'absensi' => $this->absensi($filters),
            'nilai' => $this->nilai($filters),
            default => throw new \InvalidArgumentException("Jenis laporan '{$type}' tidak dikenal."),
        };
    }

    /**
     * @param  array<string,mixed>  $f
     * @return array<string,mixed>
     */
    private function siswa(array $f): array
    {
        $rows = Student::query()
            ->when(! empty($f['status']), fn ($q) => $q->where('status', $f['status']))
            ->when(! empty($f['q']), fn ($q) => $q->where('nama', 'like', '%'.$f['q'].'%'))
            ->orderBy('nama')
            ->get(['nis', 'nisn', 'nama', 'jk', 'status'])
            ->map(fn (Student $s): array => [$s->nis, $s->nisn, $s->nama, $s->jk, $s->status]);

        return ['columns' => ['NIS', 'NISN', 'Nama', 'L/P', 'Status'], 'rows' => $rows];
    }

    /**
     * @param  array<string,mixed>  $f
     * @return array<string,mixed>
     */
    private function guru(array $f): array
    {
        $rows = Employee::query()
            ->when(! empty($f['q']), fn ($q) => $q->where('nama', 'like', '%'.$f['q'].'%'))
            ->orderBy('nama')
            ->get(['nip_nuptk', 'nama', 'jenis', 'jabatan', 'status_aktif'])
            ->map(fn (Employee $e): array => [$e->nip_nuptk, $e->nama, $e->jenis, $e->jabatan, $e->status_aktif ? 'Aktif' : 'Nonaktif']);

        return ['columns' => ['NIP/NUPTK', 'Nama', 'Jenis', 'Jabatan', 'Status'], 'rows' => $rows];
    }

    /**
     * @param  array<string,mixed>  $f
     * @return array<string,mixed>
     */
    private function kelas(array $f): array
    {
        $rows = Rombel::with('waliKelas:id,nama')
            ->withCount(['students' => fn ($q) => $q->where('class_student.status', 'aktif')])
            ->orderBy('nama')
            ->get()
            ->map(fn (Rombel $r): array => [
                $r->nama, $r->tingkat ?? '-', $r->waliKelas->nama ?? '-',
                $r->students_count, $r->kapasitas ?? '-',
            ]);

        return ['columns' => ['Rombel', 'Tingkat', 'Wali Kelas', 'Jml Siswa', 'Kapasitas'], 'rows' => $rows];
    }

    /**
     * @param  array<string,mixed>  $f
     * @return array<string,mixed>
     */
    private function mutasi(array $f): array
    {
        $rows = DB::table('class_student as cs')
            ->join('students as s', 's.id', '=', 'cs.student_id')
            ->join('rombels as r', 'r.id', '=', 'cs.rombel_id')
            ->where('s.school_id', app(TenantContext::class)->requireSchoolId())
            ->when(! empty($f['dari']), fn ($q) => $q->whereDate('cs.created_at', '>=', $f['dari']))
            ->when(! empty($f['sampai']), fn ($q) => $q->whereDate('cs.created_at', '<=', $f['sampai']))
            ->orderByDesc('cs.created_at')
            ->limit(1000)
            ->get(['s.nis', 's.nama', 'r.nama as rombel', 'cs.status', 'cs.created_at'])
            ->map(fn (object $r): array => [
                $r->nis, $r->nama, $r->rombel, $r->status,
                Carbon::parse($r->created_at)->format('d M Y'),
            ]);

        return ['columns' => ['NIS', 'Nama', 'Rombel', 'Status', 'Tanggal'], 'rows' => $rows];
    }

    /**
     * @param  array<string,mixed>  $f
     * @return array<string,mixed>
     */
    private function absensi(array $f): array
    {
        $rows = DB::table('attendances')
            ->join('students as s', 's.id', '=', 'attendances.student_id')
            ->where('attendances.school_id', app(TenantContext::class)->requireSchoolId())
            ->when(! empty($f['dari']), fn ($q) => $q->whereDate('tanggal', '>=', $f['dari']))
            ->when(! empty($f['sampai']), fn ($q) => $q->whereDate('tanggal', '<=', $f['sampai']))
            ->groupBy('s.id', 's.nis', 's.nama')
            ->orderBy('s.nama')
            ->selectRaw('s.nis, s.nama')
            ->selectRaw("SUM(CASE WHEN attendances.status='H' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN attendances.status='I' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN attendances.status='S' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN attendances.status='A' THEN 1 ELSE 0 END) as alpa")
            ->selectRaw('COUNT(*) as total')
            ->get()
            ->map(fn (object $r): array => [
                $r->nis, $r->nama, (int) $r->hadir, (int) $r->izin, (int) $r->sakit, (int) $r->alpa,
                $r->total > 0 ? round($r->hadir / $r->total * 100, 1).'%' : '-',
            ]);

        return ['columns' => ['NIS', 'Nama', 'H', 'I', 'S', 'A', '% Hadir'], 'rows' => $rows];
    }

    /**
     * @param  array<string,mixed>  $f
     * @return array<string,mixed>
     */
    private function nilai(array $f): array
    {
        $rows = DB::table('grades')
            ->join('subjects as m', 'm.id', '=', 'grades.subject_id')
            ->where('grades.school_id', app(TenantContext::class)->requireSchoolId())
            ->when(! empty($f['semester_id']), fn ($q) => $q->where('grades.semester_id', $f['semester_id']))
            ->groupBy('m.id', 'm.nama')
            ->orderBy('m.nama')
            ->selectRaw('m.nama as mapel')
            ->selectRaw('AVG(nilai) as rata_rata')
            ->selectRaw('MIN(nilai) as terendah')
            ->selectRaw('MAX(nilai) as tertinggi')
            ->selectRaw('COUNT(*) as jumlah')
            ->get()
            ->map(fn (object $r): array => [
                $r->mapel, round((float) $r->rata_rata, 1), $r->terendah, $r->tertinggi, $r->jumlah,
            ]);

        return ['columns' => ['Mata Pelajaran', 'Rata-rata', 'Terendah', 'Tertinggi', 'Jml Nilai'], 'rows' => $rows];
    }

    /**
     * @param  array<string,mixed>  $report
     */
    public function toCsv(array $report): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $report['columns'], ';');
        foreach ($report['rows'] as $row) {
            fputcsv($out, array_map(fn ($v) => (string) $v, $row), ';');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return "\xEF\xBB\xBF".($csv ?: ''); // BOM agar Excel membaca UTF-8
    }
}

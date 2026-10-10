<?php

declare(strict_types=1);

namespace App\Domains\ERapor\Services;

use App\Domains\Akademik\Services\GradeCalculationService;
use App\Models\Attendance;
use App\Models\RaporNote;
use App\Models\Semester;
use App\Models\Student;
use App\Tenancy\TenantContext;

/**
 * Komposisi data rapor cetak: nilai per mapel + predikat, rata-rata,
 * rekap absensi, dan catatan wali kelas.
 */
class ERaporService
{
    public function __construct(
        private GradeCalculationService $grades,
        private TenantContext $context,
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function raporSiswa(int $studentId, int $semesterId): array
    {
        $student = Student::findOrFail($studentId);
        $semester = Semester::findOrFail($semesterId);

        $rapor = $this->grades->rapor($studentId, $semesterId);

        $mapel = array_map(function (array $m): array {
            $na = $m['nilai_akhir'];
            $m['predikat'] = $na === null ? '-' : $this->predikat((float) $na);
            $m['tuntas'] = $na !== null && $na >= $m['kkm'];
            $m['nilai_akhir'] = $na === null ? null : round($na, 1);

            return $m;
        }, $rapor['mapel']);

        $absensi = Attendance::where('student_id', $studentId)
            ->whereDate('tanggal', '>=', $semester->tgl_mulai)
            ->whereDate('tanggal', '<=', $semester->tgl_selesai)
            ->selectRaw("SUM(CASE WHEN status='H' THEN 1 ELSE 0 END) as h")
            ->selectRaw("SUM(CASE WHEN status='I' THEN 1 ELSE 0 END) as i")
            ->selectRaw("SUM(CASE WHEN status='S' THEN 1 ELSE 0 END) as s")
            ->selectRaw("SUM(CASE WHEN status='A' THEN 1 ELSE 0 END) as a")
            ->first();

        $note = RaporNote::where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->first();

        return [
            'sekolah' => $this->context->school(),
            'siswa' => $student,
            'semester' => $semester,
            'mapel' => $mapel,
            'rata_rata' => $rapor['rata_rata'] !== null ? round($rapor['rata_rata'], 1) : null,
            'predikat_rata' => $rapor['rata_rata'] !== null ? $this->predikat($rapor['rata_rata']) : '-',
            'absensi' => [
                'H' => (int) ($absensi->h ?? 0),
                'I' => (int) ($absensi->i ?? 0),
                'S' => (int) ($absensi->s ?? 0),
                'A' => (int) ($absensi->a ?? 0),
            ],
            'catatan_wali' => $note?->catatan_wali,
        ];
    }

    public function predikat(float $nilai): string
    {
        return match (true) {
            $nilai >= 90 => 'A',
            $nilai >= 80 => 'B',
            $nilai >= 70 => 'C',
            default => 'D',
        };
    }

    public function simpanCatatan(int $studentId, int $semesterId, ?string $catatan): RaporNote
    {
        return RaporNote::updateOrCreate(
            ['student_id' => $studentId, 'semester_id' => $semesterId],
            ['catatan_wali' => $catatan, 'updated_by' => auth()->id()],
        );
    }
}

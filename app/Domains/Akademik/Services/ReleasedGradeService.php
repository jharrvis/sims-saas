<?php

declare(strict_types=1);

namespace App\Domains\Akademik\Services;

use App\Models\GradeRelease;
use App\Models\Semester;
use App\Models\Student;

/**
 * Nilai rapor untuk PWA siswa/orang tua — hanya yang sudah dirilis (FR-707).
 */
class ReleasedGradeService
{
    public function __construct(private readonly GradeCalculationService $calc) {}

    /**
     * @return array{released: bool, message?: string}|array{released: true, ...}
     */
    public function rapor(int $studentId, int $semesterId): array
    {
        $student = Student::findOrFail($studentId);
        $semester = Semester::findOrFail($semesterId);

        // Cari rombel aktif siswa pada semester ini
        $rombelId = $student->rombels()
            ->wherePivot('academic_year_id', $semester->academic_year_id)
            ->wherePivot('status', 'aktif')
            ->value('rombels.id');

        $released = $rombelId !== null && GradeRelease::where('semester_id', $semester->id)
            ->where('rombel_id', $rombelId)
            ->exists();

        if (! $released) {
            return [
                'released' => false,
                'message' => 'Nilai belum dirilis oleh sekolah.',
            ];
        }

        return [
            'released' => true,
            ...$this->calc->rapor($student->id, $semester->id),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Akademik\Services;

use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Rombel;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

/**
 * Perhitungan nilai akhir berbobot (FR-704).
 * Nilai akhir = Σ(nilai_komponen × bobot / 100).
 */
class GradeCalculationService
{
    /**
     * Nilai akhir satu siswa untuk satu mapel + semester.
     */
    public function finalGrade(int $studentId, int $subjectId, int $semesterId): ?float
    {
        $components = $this->componentsFor($subjectId);
        if ($components->isEmpty()) {
            return null;
        }

        $grades = Grade::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->whereIn('grade_component_id', $components->pluck('id'))
            ->get()
            ->keyBy('grade_component_id');

        $total = 0.0;
        $bobotTerisi = 0.0;

        foreach ($components as $c) {
            $g = $grades->get($c->id);
            if ($g === null) {
                continue;
            }
            $bobot = (float) $c->bobot;
            $total += (float) $g->nilai * $bobot / 100;
            $bobotTerisi += $bobot;
        }

        if ($bobotTerisi <= 0) {
            return null;
        }

        // Normalisasi jika belum semua komponen terisi
        return round($total / $bobotTerisi * 100, 2);
    }

    /**
     * Leger: nilai akhir semua siswa dalam satu rombel per mapel (FR-706).
     *
     * @return Collection<int, array{student_id: int<0, max>, nama: string, nis: string, nilai_akhir: float|null}>
     */
    public function leger(int $rombelId, int $subjectId, int $semesterId): Collection
    {
        $rombel = Rombel::findOrFail($rombelId);

        $students = $rombel->students()
            ->wherePivot('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return $students->map(fn (Student $s) => [
            'student_id' => $s->id,
            'nama' => $s->nama,
            'nis' => $s->nis,
            'nilai_akhir' => $this->finalGrade($s->id, $subjectId, $semesterId),
        ]);
    }

    /**
     * Rapor satu siswa: nilai akhir per mapel + rata-rata (FR-705).
     *
     * @return array{mapel: array<int, array{kode:string, nama:string, kkm:float, nilai_akhir:?float}>, rata_rata:?float}
     */
    public function rapor(int $studentId, int $semesterId): array
    {
        $subjects = Subject::orderBy('nama')->get();

        $mapel = [];
        $total = 0.0;
        $count = 0;

        foreach ($subjects as $subject) {
            $na = $this->finalGrade($studentId, $subject->id, $semesterId);
            $mapel[] = [
                'kode' => $subject->kode,
                'nama' => $subject->nama,
                'kkm' => (float) $subject->kkm_default,
                'nilai_akhir' => $na,
            ];
            if ($na !== null) {
                $total += $na;
                $count++;
            }
        }

        return [
            'mapel' => $mapel,
            'rata_rata' => $count > 0 ? round($total / $count, 2) : null,
        ];
    }

    /**
     * @return Collection<int, GradeComponent>
     */
    private function componentsFor(int $subjectId): Collection
    {
        // Komponen spesifik mapel, fallback ke komponen default sekolah
        $specific = GradeComponent::where('subject_id', $subjectId)->orderBy('urutan')->get();

        return $specific->isNotEmpty()
            ? $specific
            : GradeComponent::whereNull('subject_id')->orderBy('urutan')->get();
    }
}

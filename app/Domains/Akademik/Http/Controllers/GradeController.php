<?php

declare(strict_types=1);

namespace App\Domains\Akademik\Http\Controllers;

use App\Domains\Akademik\Services\GradeCalculationService;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\GradeRelease;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API nilai v1 (FR-701–707).
 */
class GradeController extends Controller
{
    // ---------- Komponen penilaian ----------

    public function componentIndex(): JsonResponse
    {
        return response()->json(
            GradeComponent::with('subject:id,nama')
                ->orderBy('subject_id')->orderBy('urutan')
                ->paginate(20)
        );
    }

    public function componentStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'nama' => ['required', 'string', 'max:50'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'urutan' => ['sometimes', 'integer', 'min:0'],
        ]);

        return response()->json(GradeComponent::create($data), 201);
    }

    // ---------- Input nilai ----------

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grade_component_id' => ['required', 'integer', 'exists:grade_components,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.nilai' => ['required', 'numeric', 'min:0', 'max:100'], // FR-703
        ]);

        /** @var array<int,array{student_id:int,nilai:float}> $records */
        $records = $v['records'];

        foreach ($records as $r) {
            Grade::updateOrCreate(
                [
                    'student_id' => $r['student_id'],
                    'subject_id' => $v['subject_id'],
                    'grade_component_id' => $v['grade_component_id'],
                    'semester_id' => $v['semester_id'],
                ],
                [
                    'nilai' => $r['nilai'],
                    'input_by' => $request->user()->id,
                ],
            );
        }

        return response()->json(['message' => 'Nilai tersimpan.', 'count' => count($records)]);
    }

    // ---------- Leger ----------

    public function leger(Request $request, GradeCalculationService $calc): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
        ]);

        return response()->json([
            'data' => $calc->leger((int) $v['rombel_id'], (int) $v['subject_id'], (int) $v['semester_id']),
        ]);
    }

    // ---------- Rapor ----------

    public function rapor(Request $request, GradeCalculationService $calc): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
        ]);

        $student = Student::findOrFail($v['student_id']);

        return response()->json([
            'student' => ['id' => $student->id, 'nama' => $student->nama, 'nis' => $student->nis],
            ...$calc->rapor($student->id, (int) $v['semester_id']),
        ]);
    }

    // ---------- Rilis ----------

    public function release(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
        ]);

        $release = GradeRelease::updateOrCreate(
            ['semester_id' => $v['semester_id'], 'rombel_id' => $v['rombel_id']],
            ['dirilis_pada' => now(), 'dirilis_oleh' => $request->user()->id],
        );

        return response()->json($release, 201);
    }

    /**
     * Nilai untuk PWA siswa/orang tua — hanya yang sudah dirilis (FR-707).
     */
    public function forStudent(Request $request, GradeCalculationService $calc): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
        ]);

        $student = Student::findOrFail($v['student_id']);
        $semester = Semester::findOrFail($v['semester_id']);

        // Cari rombel aktif siswa pada semester ini
        $rombelId = $student->rombels()
            ->wherePivot('academic_year_id', $semester->academic_year_id)
            ->wherePivot('status', 'aktif')
            ->value('rombels.id');

        $released = $rombelId !== null && GradeRelease::where('semester_id', $semester->id)
            ->where('rombel_id', $rombelId)
            ->exists();

        if (! $released) {
            return response()->json([
                'released' => false,
                'message' => 'Nilai belum dirilis oleh sekolah.',
            ]);
        }

        return response()->json([
            'released' => true,
            ...$calc->rapor($student->id, $semester->id),
        ]);
    }
}

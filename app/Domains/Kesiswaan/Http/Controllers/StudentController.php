<?php

declare(strict_types=1);

namespace App\Domains\Kesiswaan\Http\Controllers;

use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API siswa v1 (FR-101–106).
 */
class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $students = Student::query()
            ->when($request->string('search')->isNotEmpty(), fn ($q) => $q->where(function ($w) use ($request): void {
                $s = '%'.$request->string('search').'%';
                $w->where('nama', 'like', $s)->orWhere('nis', 'like', $s);
            }))
            ->when($request->string('status')->isNotEmpty(), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('nama')
            ->paginate(20);

        return response()->json($students);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $student = Student::create($data);

        return response()->json($student, 201);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json($student->load('guardians'));
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $student->update($this->validated($request, $student->id));

        return response()->json($student->fresh());
    }

    public function destroy(Student $student): JsonResponse
    {
        // FR-106: nonaktifkan (soft delete), histori tetap tersimpan
        $student->delete();

        return response()->json(['message' => 'Siswa dinonaktifkan.']);
    }

    /**
     * @return array<string,mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('students')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($ignoreId)],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'jk' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tgl_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', Rule::in(Student::AGAMAS)],
            'alamat' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        return $data;
    }
}

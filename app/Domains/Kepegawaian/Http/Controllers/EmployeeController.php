<?php

declare(strict_types=1);

namespace App\Domains\Kepegawaian\Http\Controllers;

use App\Domains\Kepegawaian\Services\EmployeeAccountSync;
use App\Models\Employee;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API guru & pegawai v1 (FR-301–305).
 */
class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employees = Employee::query()
            ->when($request->string('search')->isNotEmpty(), fn ($q) => $q->where(function ($w) use ($request): void {
                $s = '%'.$request->string('search').'%';
                $w->where('nama', 'like', $s)->orWhere('nip_nuptk', 'like', $s);
            }))
            ->when($request->string('jenis')->isNotEmpty(), fn ($q) => $q->where('jenis', $request->string('jenis')))
            ->orderBy('nama')
            ->paginate(20);

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Employee::create($this->validated($request)), 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $employee->update($this->validated($request, $employee));

        // Status akun login tertaut mengikuti status_aktif (QA B-01).
        app(EmployeeAccountSync::class)->syncMembershipStatus($employee);

        return response()->json($employee->fresh());
    }

    public function destroy(Employee $employee): JsonResponse
    {
        // Nonaktifkan (soft delete)
        $employee->delete();

        app(EmployeeAccountSync::class)->syncMembershipStatus($employee);

        return response()->json(['message' => 'Guru/pegawai dinonaktifkan.']);
    }

    /**
     * @return array<string,mixed>
     */
    private function validated(Request $request, ?Employee $employee = null): array
    {
        $schoolId = $employee?->school_id ?? app(TenantContext::class)->requireSchoolId();

        /** @var array<string,mixed> $data */
        $data = $request->validate([
            // Selaras form web: identitas unik per sekolah (QA B-10/B-19).
            'nip_nuptk' => ['nullable', 'string', 'max:30', Rule::unique('employees')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($employee?->id)],
            'nik' => ['nullable', 'string', 'digits:16', Rule::unique('employees')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($employee?->id)],
            'nama' => ['required', 'string', 'max:150'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tgl_lahir' => ['nullable', 'date'],
            'jk' => ['nullable', Rule::in(['L', 'P'])],
            'agama' => ['nullable', Rule::in(Student::AGAMAS)],
            'alamat' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('employees')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($employee?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'jenis' => ['required', Rule::in(['guru_mapel', 'guru_kelas', 'tendik'])],
            'status_kepegawaian' => ['required', Rule::in(['pns', 'pppk', 'honorer', 'kontrak'])],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'pendidikan_terakhir' => ['nullable', Rule::in(array_keys(Employee::PENDIDIKAN))],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'perguruan_tinggi' => ['nullable', 'string', 'max:150'],
            'tahun_lulus' => ['nullable', 'integer', 'min:1950', 'max:'.date('Y')],
            'tmt' => ['nullable', 'date'],
            'golongan' => ['nullable', Rule::in(Employee::GOLONGAN)],
            'sertifikasi' => ['nullable', Rule::in(array_keys(Employee::SERTIFIKASI))],
            'no_sertifikat' => ['nullable', 'string', 'max:50'],
            'status_aktif' => ['sometimes', 'boolean'],
        ]);

        return $data;
    }
}

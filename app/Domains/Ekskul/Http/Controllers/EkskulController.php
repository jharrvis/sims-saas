<?php

declare(strict_types=1);

namespace App\Domains\Ekskul\Http\Controllers;

use App\Models\Achievement;
use App\Models\Extracurricular;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Ekstrakurikuler & Prestasi v1 (modul add-on — di belakang middleware module:ekstrakurikuler).
 */
class EkskulController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Extracurricular::with('pembina:id,nama')
                ->withCount('members')
                ->orderBy('nama')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jadwal' => ['nullable', 'string', 'max:100'],
            'pembina_id' => ['nullable', 'integer', 'exists:employees,id'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        return response()->json(Extracurricular::create($data), 201);
    }

    public function show(Extracurricular $ekskul): JsonResponse
    {
        return response()->json(
            $ekskul->load(['pembina:id,nama', 'members' => fn ($q) => $q->wherePivot('status', 'aktif')])
        );
    }

    public function addMember(Request $request, Extracurricular $ekskul): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $ekskul->members()->syncWithoutDetaching([
            $data['student_id'] => [
                'school_id' => app(TenantContext::class)->requireSchoolId(),
                'tanggal_gabung' => now()->toDateString(),
                'status' => 'aktif',
            ],
        ]);

        return response()->json(['message' => 'Anggota ditambahkan.']);
    }

    public function removeMember(Extracurricular $ekskul, int $studentId): JsonResponse
    {
        $ekskul->members()->updateExistingPivot($studentId, ['status' => 'keluar']);

        return response()->json(['message' => 'Anggota dikeluarkan.']);
    }

    public function achievementIndex(Request $request): JsonResponse
    {
        return response()->json(
            Achievement::with('student:id,nis,nama')
                ->when($request->filled('tingkat'), fn ($q) => $q->where('tingkat', $request->string('tingkat')))
                ->orderByDesc('tanggal')
                ->paginate(20)
        );
    }

    public function achievementStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'nama' => ['required', 'string', 'max:200'],
            'kategori' => ['required', Rule::in(['akademik', 'non_akademik'])],
            'tingkat' => ['required', Rule::in(array_keys(Achievement::TINGKAT))],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(Achievement::create($data), 201);
    }
}

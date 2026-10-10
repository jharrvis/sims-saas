<?php

declare(strict_types=1);

namespace App\Domains\Organisasi\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationActivity;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Organisasi v1 (modul add-on — di belakang middleware module:organisasi).
 */
class OrganisasiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Organization::with('pembina:id,nama')
                ->withCount([
                    'members' => fn ($q) => $q->where('organization_members.status', 'aktif'),
                ])
                ->orderBy('nama')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(array_keys(Organization::JENIS))],
            'pembina_id' => ['nullable', 'integer', 'exists:employees,id'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        return response()->json(Organization::create($data), 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        return response()->json(
            $organization->load([
                'pembina:id,nama',
                'members' => fn ($q) => $q->wherePivot('status', 'aktif'),
                'activities' => fn ($q) => $q->orderByDesc('tgl_mulai'),
            ])
        );
    }

    public function addMember(Request $request, Organization $organization): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'periode' => ['nullable', 'string', 'max:20'],
        ]);

        $organization->members()->syncWithoutDetaching([
            $data['student_id'] => [
                'school_id' => app(TenantContext::class)->requireSchoolId(),
                'jabatan' => $data['jabatan'] ?? null,
                'periode' => $data['periode'] ?? null,
                'status' => 'aktif',
            ],
        ]);

        return response()->json(['message' => 'Pengurus ditambahkan.']);
    }

    public function addActivity(Request $request, Organization $organization): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:200'],
            'tgl_mulai' => ['nullable', 'date'],
            'tgl_selesai' => ['nullable', 'date', 'after_or_equal:tgl_mulai'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::in(array_keys(OrganizationActivity::STATUS))],
        ]);

        return response()->json($organization->activities()->create($data), 201);
    }
}

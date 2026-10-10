<?php

declare(strict_types=1);

namespace App\Domains\Transportasi\Http\Controllers;

use App\Models\TransportRoute;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API Antar-Jemput v1 (modul add-on — di belakang middleware module:transportasi).
 */
class TransportasiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            TransportRoute::withCount([
                'members' => fn ($q) => $q->where('transport_members.status', 'aktif'),
            ])->orderBy('nama')->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'sopir' => ['nullable', 'string', 'max:150'],
            'no_kendaraan' => ['nullable', 'string', 'max:30'],
            'kapasitas' => ['required', 'integer', 'min:0', 'max:100'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        return response()->json(TransportRoute::create($data), 201);
    }

    public function show(TransportRoute $route): JsonResponse
    {
        return response()->json(
            $route->load([
                'stops',
                'members' => fn ($q) => $q->wherePivot('status', 'aktif'),
            ])
        );
    }

    public function addStop(Request $request, TransportRoute $route): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jam_berangkat' => ['nullable', 'date_format:H:i'],
        ]);

        $max = $route->stops()->max('urutan') ?? 0;

        return response()->json(
            $route->stops()->create([
                'nama' => $data['nama'],
                'urutan' => $max + 1,
                'jam_berangkat' => $data['jam_berangkat'] ?? null,
            ]),
            201
        );
    }

    public function addMember(Request $request, TransportRoute $route): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'transport_stop_id' => ['nullable', 'integer', 'exists:transport_stops,id'],
        ]);

        if ($route->isPenuh()) {
            return response()->json(['message' => 'Kapasitas rute sudah penuh.', 'code' => 'RUTE_PENUH'], 422);
        }

        $route->members()->syncWithoutDetaching([
            $data['student_id'] => [
                'school_id' => app(TenantContext::class)->requireSchoolId(),
                'transport_stop_id' => $data['transport_stop_id'] ?? null,
                'status' => 'aktif',
            ],
        ]);

        return response()->json(['message' => 'Penumpang ditambahkan.']);
    }

    public function removeMember(TransportRoute $route, int $studentId): JsonResponse
    {
        $route->members()->updateExistingPivot($studentId, ['status' => 'berhenti']);

        return response()->json(['message' => 'Penumpang dikeluarkan.']);
    }
}

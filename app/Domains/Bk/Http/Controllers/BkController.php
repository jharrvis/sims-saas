<?php

declare(strict_types=1);

namespace App\Domains\Bk\Http\Controllers;

use App\Models\CounselingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API BK & Konseling v1 (modul add-on — di belakang middleware module:bk_konseling).
 */
class BkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            CounselingSession::with(['student:id,nis,nama', 'counselor:id,name'])
                ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->orderBy('tanggal')
                ->paginate(20)
        );
    }

    public function show(CounselingSession $session): JsonResponse
    {
        return response()->json($session->load(['student:id,nis,nama', 'counselor:id,name']));
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', Rule::in(['individu', 'kelompok', 'klasikal'])],
            'topik' => ['required', 'string', 'max:200'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:200'],
            'status' => ['sometimes', Rule::in(['terjadwal', 'selesai', 'batal'])],
        ]);

        $data['counselor_id'] = $request->user()->id;

        return response()->json(CounselingSession::create($data), 201);
    }

    public function update(Request $request, CounselingSession $session): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'tanggal' => ['sometimes', 'date'],
            'jenis' => ['sometimes', Rule::in(['individu', 'kelompok', 'klasikal'])],
            'topik' => ['sometimes', 'string', 'max:200'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:200'],
            'status' => ['sometimes', Rule::in(['terjadwal', 'selesai', 'batal'])],
        ]);

        $session->update($data);

        return response()->json($session->fresh());
    }

    public function destroy(CounselingSession $session): JsonResponse
    {
        $session->delete();

        return response()->json(null, 204);
    }
}

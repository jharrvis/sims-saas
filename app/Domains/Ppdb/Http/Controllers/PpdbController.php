<?php

declare(strict_types=1);

namespace App\Domains\Ppdb\Http\Controllers;

use App\Domains\Ppdb\Services\PpdbService;
use App\Models\PpdbApplicant;
use App\Models\PpdbPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API PPDB v1 (modul add-on — di belakang middleware module:ppdb).
 */
class PpdbController extends Controller
{
    // ---------- Periode ----------

    public function periodIndex(): JsonResponse
    {
        return response()->json(
            PpdbPeriod::with('academicYear:id,nama')
                ->withCount('applicants')
                ->orderByDesc('tgl_buka')
                ->paginate(20)
        );
    }

    public function periodStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'nama' => ['required', 'string', 'max:100'],
            'tgl_buka' => ['required', 'date'],
            'tgl_tutup' => ['required', 'date', 'after_or_equal:tgl_buka'],
            'kuota' => ['sometimes', 'integer', 'min:0'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        return response()->json(PpdbPeriod::create($data), 201);
    }

    // ---------- Pendaftar ----------

    public function applicantIndex(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'ppdb_period_id' => ['required', 'integer', 'exists:ppdb_periods,id'],
            'status' => ['sometimes', Rule::in(array_keys(PpdbApplicant::STATUS))],
        ]);

        return response()->json(
            PpdbApplicant::where('ppdb_period_id', $v['ppdb_period_id'])
                ->when(isset($v['status']), fn ($q) => $q->where('status', $v['status']))
                ->orderByDesc('created_at')
                ->paginate(20)
        );
    }

    public function applicantStore(Request $request, PpdbService $service): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'ppdb_period_id' => ['required', 'integer', 'exists:ppdb_periods,id'],
            'nama' => ['required', 'string', 'max:150'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'jk' => ['required', Rule::in(['L', 'P'])],
            'tgl_lahir' => ['nullable', 'date'],
            'asal_sekolah' => ['nullable', 'string', 'max:150'],
            'nama_ortu' => ['nullable', 'string', 'max:150'],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
        ]);

        $applicant = PpdbApplicant::create([
            ...$data,
            'no_pendaftaran' => $service->generateNoPendaftaran(),
            'status' => 'draft',
        ]);

        return response()->json($applicant, 201);
    }

    public function applicantShow(PpdbApplicant $applicant): JsonResponse
    {
        return response()->json($applicant->load(['period:id,nama', 'documents', 'selection']));
    }

    public function applicantStatus(Request $request, PpdbApplicant $applicant, PpdbService $service): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'status' => ['required', Rule::in(['submitted', 'verified', 'accepted', 'rejected'])],
            'catatan' => ['nullable', 'string'],
        ]);

        try {
            $result = $service->setStatus($applicant, $v['status'], $v['catatan'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function applicantSelect(Request $request, PpdbApplicant $applicant): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nilai_tes' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_wawancara' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'keputusan' => ['nullable', Rule::in(['lulus', 'tidak_lulus', 'cadangan'])],
        ]);

        $nilaiAkhir = null;
        if (isset($data['nilai_tes'], $data['nilai_wawancara'])) {
            $nilaiAkhir = round(((float) $data['nilai_tes'] + (float) $data['nilai_wawancara']) / 2, 2);
        }

        $selection = $applicant->selection()->updateOrCreate(
            ['ppdb_applicant_id' => $applicant->id],
            [...$data, 'nilai_akhir' => $nilaiAkhir, 'dinilai_oleh' => $request->user()->id],
        );

        return response()->json($selection);
    }

    public function applicantEnroll(PpdbApplicant $applicant, PpdbService $service): JsonResponse
    {
        try {
            $student = $service->enroll($applicant);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Daftar ulang berhasil.',
            'student' => $student,
        ], 201);
    }
}

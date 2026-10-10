<?php

declare(strict_types=1);

namespace App\Domains\Tatib\Http\Controllers;

use App\Models\Violation;
use App\Models\ViolationType;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Tata Tertib v1 (modul add-on — di belakang middleware module:tata_tertib).
 */
class TatibController extends Controller
{
    public function typeIndex(): JsonResponse
    {
        return response()->json(ViolationType::where('aktif', true)->orderBy('nama')->get());
    }

    public function typeStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', Rule::in(['ringan', 'sedang', 'berat'])],
            'poin' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        return response()->json(ViolationType::create($data), 201);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Violation::with(['student:id,nis,nama', 'type:id,nama,kategori,poin'])
                ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))
                ->orderByDesc('tanggal')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'violation_type_id' => ['required', 'integer', 'exists:violation_types,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:200'],
        ]);

        $data['dilaporkan_oleh'] = $request->user()->id;

        return response()->json(Violation::create($data)->load('type:id,nama,poin'), 201);
    }

    public function destroy(Violation $violation): JsonResponse
    {
        $violation->delete();

        return response()->json(null, 204);
    }

    /**
     * Rekap total poin per siswa.
     */
    public function rekap(TenantContext $context): JsonResponse
    {
        $rows = Violation::join('violation_types as t', 't.id', '=', 'violations.violation_type_id')
            ->join('students as s', 's.id', '=', 'violations.student_id')
            ->where('violations.school_id', $context->requireSchoolId())
            ->groupBy('s.id', 's.nis', 's.nama')
            ->orderByDesc('total_poin')
            ->selectRaw('s.id, s.nis, s.nama, SUM(t.poin) as total_poin, COUNT(*) as jumlah')
            ->get();

        return response()->json($rows);
    }
}

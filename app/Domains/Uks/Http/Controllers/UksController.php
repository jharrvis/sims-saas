<?php

declare(strict_types=1);

namespace App\Domains\Uks\Http\Controllers;

use App\Models\HealthVisit;
use App\Models\Medicine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API UKS & Kesehatan v1 (modul add-on — di belakang middleware module:uks).
 */
class UksController extends Controller
{
    public function visitIndex(Request $request): JsonResponse
    {
        return response()->json(
            HealthVisit::with('student:id,nis,nama')
                ->when($request->filled('rujukan'), fn ($q) => $q->where('rujukan', $request->string('rujukan')))
                ->orderByDesc('tgl')
                ->paginate(20)
        );
    }

    public function visitStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'tgl' => ['required', 'date'],
            'keluhan' => ['required', 'string', 'max:2000'],
            'diagnosa' => ['nullable', 'string', 'max:200'],
            'tindakan' => ['nullable', 'string', 'max:2000'],
            'suhu' => ['nullable', 'numeric', 'min:30', 'max:45'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'rujukan' => ['sometimes', Rule::in(array_keys(HealthVisit::RUJUKAN))],
        ]);

        $data['ditangani_oleh'] = $request->user()->id;

        return response()->json(HealthVisit::create($data), 201);
    }

    public function medicineIndex(Request $request): JsonResponse
    {
        return response()->json(
            Medicine::when($request->filled('rendah'), fn ($q) => $q->whereColumn('stok', '<=', 'stok_minimum'))
                ->orderBy('nama')
                ->paginate(20)
        );
    }

    public function medicineStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'satuan' => ['sometimes', 'string', 'max:30'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['sometimes', 'integer', 'min:0'],
        ]);

        return response()->json(Medicine::create($data), 201);
    }

    public function medicineUse(Request $request, Medicine $medicine): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
            'health_visit_id' => ['nullable', 'integer', 'exists:health_visits,id'],
        ]);

        $usage = $medicine->pakai(
            $data['jumlah'],
            $request->user()->id,
            $data['health_visit_id'] ?? null
        );

        return response()->json($usage, 201);
    }
}

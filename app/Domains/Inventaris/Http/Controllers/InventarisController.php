<?php

declare(strict_types=1);

namespace App\Domains\Inventaris\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Inventaris & Aset v1 (modul add-on — di belakang middleware module:inventaris).
 */
class InventarisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Asset::with('category:id,nama')
                ->when($request->filled('kondisi'), fn ($q) => $q->where('kondisi', $request->string('kondisi')))
                ->orderBy('nama')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'asset_category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'kode' => ['required', 'string', 'max:50', Rule::unique('assets')->where('school_id', $schoolId)],
            'nama' => ['required', 'string', 'max:200'],
            'kondisi' => ['sometimes', Rule::in(array_keys(Asset::KONDISI))],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'tgl_beli' => ['nullable', 'date'],
            'harga_beli' => ['nullable', 'numeric', 'min:0'],
            'sumber_dana' => ['nullable', 'string', 'max:100'],
        ]);

        $asset = Asset::create($data);
        $asset->catatMutasi('masuk', ['ke' => $data['lokasi'] ?? null, 'catatan' => 'Pengadaan awal'], $request->user()->id);

        return response()->json($asset, 201);
    }

    public function show(Asset $asset): JsonResponse
    {
        return response()->json(
            $asset->load(['category:id,nama', 'mutations' => fn ($q) => $q->orderByDesc('tgl')])
        );
    }

    public function mutate(Request $request, Asset $asset): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'jenis' => ['required', Rule::in(['pindah', 'kondisi', 'keluar'])],
            'dari' => ['nullable', 'string', 'max:150'],
            'ke' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'tgl' => ['nullable', 'date'],
        ]);

        $mutation = $asset->catatMutasi($data['jenis'], $data, $request->user()->id);

        return response()->json($mutation, 201);
    }

    public function categoryIndex(): JsonResponse
    {
        return response()->json(AssetCategory::orderBy('nama')->paginate(20));
    }

    public function categoryStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:150'],
        ]);

        return response()->json(AssetCategory::create($data), 201);
    }
}

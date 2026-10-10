<?php

declare(strict_types=1);

namespace App\Domains\Akademik\Http\Controllers;

use App\Models\Rombel;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API rombel v1 (FR-401, FR-303).
 */
class RombelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rombels = Rombel::query()
            ->with(['waliKelas:id,nama'])
            ->withCount('students')
            ->when($request->integer('academic_year_id'), fn ($q, $id) => $q->where('academic_year_id', $id))
            ->orderBy('tingkat')->orderBy('nama')
            ->paginate(20);

        return response()->json($rombels);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Rombel::create($this->validated($request)), 201);
    }

    public function show(Rombel $rombel): JsonResponse
    {
        return response()->json($rombel->load(['waliKelas:id,nama', 'students:id,nama,nis']));
    }

    public function update(Request $request, Rombel $rombel): JsonResponse
    {
        $rombel->update($this->validated($request, $rombel->id));

        return response()->json($rombel->fresh());
    }

    public function destroy(Rombel $rombel): JsonResponse
    {
        $rombel->delete();

        return response()->json(['message' => 'Rombel diarsipkan.']);
    }

    /**
     * @return array<string,mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'tingkat' => ['required', 'string', 'max:10'],
            'nama' => ['required', 'string', 'max:50', Rule::unique('rombels')->where(
                fn ($q) => $q->where('school_id', $schoolId)
                    ->where('academic_year_id', $request->integer('academic_year_id'))
            )->ignore($ignoreId)],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:100'],
            'wali_kelas_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        return $data;
    }
}

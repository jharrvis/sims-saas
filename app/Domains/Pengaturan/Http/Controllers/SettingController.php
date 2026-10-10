<?php

declare(strict_types=1);

namespace App\Domains\Pengaturan\Http\Controllers;

use App\Models\AcademicYear;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Pengaturan v1: profil sekolah + tahun ajaran.
 */
class SettingController extends Controller
{
    public function profile(TenantContext $context): JsonResponse
    {
        return response()->json($context->school());
    }

    public function updateProfile(Request $request, TenantContext $context): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['sometimes', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'jenjang' => ['sometimes', Rule::in(['SD', 'SMP', 'SMA', 'SMK'])],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
        ]);

        $context->school()->update($data);

        return response()->json($context->school()->fresh());
    }

    public function academicYears(): JsonResponse
    {
        return response()->json(AcademicYear::orderByDesc('tgl_mulai')->get());
    }

    public function activateYear(AcademicYear $tahun): JsonResponse
    {
        AcademicYear::query()->update(['aktif' => false]);
        $tahun->update(['aktif' => true]);

        return response()->json($tahun->fresh());
    }
}

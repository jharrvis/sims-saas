<?php

declare(strict_types=1);

namespace App\Domains\ERapor\Http\Controllers;

use App\Domains\ERapor\Services\ERaporService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API E-Rapor v1 (modul add-on — di belakang middleware module:e_rapor).
 */
class ERaporController extends Controller
{
    public function show(int $student, int $semester, ERaporService $service): JsonResponse
    {
        return response()->json($service->raporSiswa($student, $semester));
    }

    public function simpanCatatan(Request $request, int $student, int $semester, ERaporService $service): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'catatan_wali' => ['nullable', 'string', 'max:2000'],
        ]);

        $note = $service->simpanCatatan($student, $semester, $data['catatan_wali'] ?? null);

        return response()->json($note, 201);
    }
}

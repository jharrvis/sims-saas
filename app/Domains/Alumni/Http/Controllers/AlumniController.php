<?php

declare(strict_types=1);

namespace App\Domains\Alumni\Http\Controllers;

use App\Models\Alumni;
use App\Models\AlumniCareer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Alumni & Karier v1 (modul add-on — di belakang middleware module:alumni).
 */
class AlumniController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Alumni::with('careers')
                ->when($request->filled('tahun_lulus'), fn ($q) => $q->where('tahun_lulus', $request->string('tahun_lulus')))
                ->orderByDesc('tahun_lulus')
                ->orderBy('nama')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'nama' => ['required', 'string', 'max:150'],
            'tahun_lulus' => ['nullable', 'string', 'max:9'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json(Alumni::create($data), 201);
    }

    public function show(Alumni $alumni): JsonResponse
    {
        return response()->json($alumni->load('careers'));
    }

    public function addCareer(Request $request, Alumni $alumni): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(AlumniCareer::STATUS))],
            'institusi' => ['nullable', 'string', 'max:200'],
            'jurusan_posisi' => ['nullable', 'string', 'max:200'],
            'tahun_mulai' => ['nullable', 'string', 'max:9'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json($alumni->careers()->create($data), 201);
    }

    /**
     * Statistik tracer study: distribusi status karier terakhir per alumni.
     */
    public function statistik(): JsonResponse
    {
        $distribusi = Alumni::with('careers')
            ->get()
            ->map(fn (Alumni $a) => $a->karierTerakhir()->status ?? 'belum_terdata')
            ->countBy()
            ->sortDesc()
            ->all();

        return response()->json([
            'total_alumni' => Alumni::count(),
            'distribusi' => $distribusi,
        ]);
    }
}

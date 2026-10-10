<?php

declare(strict_types=1);

namespace App\Domains\Pengumuman\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Pengumuman v1 (modul add-on — di belakang middleware module:pengumuman).
 */
class AnnouncementController extends Controller
{
    /**
     * Daftar pengumuman yang sedang tayang (untuk PWA siswa/orang tua).
     */
    public function index(Request $request): JsonResponse
    {
        $items = Announcement::tayang()
            ->when($request->boolean('penting_saja'), fn ($q) => $q->where('penting', true))
            ->orderByDesc('penting')
            ->orderByDesc('tayang_dari')
            ->paginate(20);

        return response()->json($items);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json($announcement);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:10000'],
            'target_type' => ['required', Rule::in(['semua', 'peran', 'kelas'])],
            'target_data' => ['nullable', 'array'],
            'penting' => ['sometimes', 'boolean'],
            'tayang_dari' => ['required', 'date'],
            'tayang_sampai' => ['nullable', 'date', 'after_or_equal:tayang_dari'],
        ]);

        $data['dibuat_oleh'] = $request->user()->id;

        return response()->json(Announcement::create($data), 201);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'judul' => ['sometimes', 'string', 'max:200'],
            'isi' => ['sometimes', 'string', 'max:10000'],
            'target_type' => ['sometimes', Rule::in(['semua', 'peran', 'kelas'])],
            'target_data' => ['nullable', 'array'],
            'penting' => ['sometimes', 'boolean'],
            'tayang_dari' => ['sometimes', 'date'],
            'tayang_sampai' => ['nullable', 'date', 'after_or_equal:tayang_dari'],
        ]);

        $announcement->update($data);

        return response()->json($announcement);
    }

    public function publish(Announcement $announcement): JsonResponse
    {
        $announcement->update(['status' => 'tayang']);

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json(null, 204);
    }
}

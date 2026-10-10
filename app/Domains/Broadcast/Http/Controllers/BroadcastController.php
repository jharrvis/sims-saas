<?php

declare(strict_types=1);

namespace App\Domains\Broadcast\Http\Controllers;

use App\Domains\Broadcast\Services\BroadcastService;
use App\Models\Broadcast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Broadcast v1 (modul add-on — di belakang middleware module:broadcast).
 */
class BroadcastController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Broadcast::with('pembuat:id,name')
                ->withCount('logs')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->orderByDesc('created_at')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'pesan' => ['required', 'string', 'max:5000'],
            'channel' => ['required', Rule::in(['whatsapp', 'notifikasi'])],
            'target_type' => ['required', Rule::in(['semua', 'siswa', 'guru', 'rombel'])],
            'target_data' => ['nullable', 'array'],
            'dijadwalkan_pada' => ['nullable', 'date'],
        ]);

        $data['dibuat_oleh'] = $request->user()->id;
        $data['status'] = ($data['dijadwalkan_pada'] ?? null) ? 'terjadwal' : 'draf';

        return response()->json(Broadcast::create($data), 201);
    }

    public function show(Broadcast $broadcast): JsonResponse
    {
        return response()->json(
            $broadcast->load(['pembuat:id,name', 'logs'])
        );
    }

    public function send(Broadcast $broadcast, BroadcastService $service): JsonResponse
    {
        abort_if(! $broadcast->isSiapKirim(), 422, 'Broadcast belum siap dikirim.');

        $terkirim = $service->kirim($broadcast);

        return response()->json([
            'message' => "Broadcast dikirim ke {$terkirim} penerima.",
            'broadcast' => $broadcast->fresh(),
        ]);
    }
}

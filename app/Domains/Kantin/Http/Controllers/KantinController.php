<?php

declare(strict_types=1);

namespace App\Domains\Kantin\Http\Controllers;

use App\Models\CanteenProduct;
use App\Models\CanteenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API Kantin Digital v1 (modul add-on — di belakang middleware module:kantin).
 */
class KantinController extends Controller
{
    public function productIndex(): JsonResponse
    {
        return response()->json(
            CanteenProduct::where('aktif', true)->orderBy('nama')->paginate(20)
        );
    }

    public function productStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        return response()->json(CanteenProduct::create($data), 201);
    }

    public function checkout(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'keranjang' => ['required', 'array', 'min:1'],
            'keranjang.*.product_id' => ['required', 'integer', 'exists:canteen_products,id'],
            'keranjang.*.qty' => ['required', 'integer', 'min:1'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'bayar' => ['required', 'numeric', 'min:0'],
        ]);

        /** @var array<int, array{product_id: int, qty: int}> $keranjang */
        $keranjang = $data['keranjang'];

        $trx = CanteenTransaction::checkout(
            $keranjang,
            $data['student_id'] ?? null,
            (float) $data['bayar'],
            $request->user()->id
        );

        return response()->json($trx->load('items'), 201);
    }

    public function transactionIndex(Request $request): JsonResponse
    {
        return response()->json(
            CanteenTransaction::with(['student:id,nama', 'items'])
                ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tgl', (string) $request->string('tanggal')))
                ->orderByDesc('tgl')
                ->paginate(20)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Keuangan\Http\Controllers;

use App\Domains\Keuangan\Services\KeuanganService;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Keuangan v1 (modul add-on — di belakang middleware module:keuangan).
 */
class KeuanganController extends Controller
{
    // ---------- Jenis tagihan ----------

    public function feeTypeIndex(): JsonResponse
    {
        return response()->json(FeeType::orderBy('nama')->paginate(20));
    }

    public function feeTypeStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'periode' => ['required', Rule::in(FeeType::PERIODES)],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        return response()->json(FeeType::create($data), 201);
    }

    // ---------- Tagihan ----------

    public function invoiceIndex(Request $request): JsonResponse
    {
        return response()->json(
            Invoice::with(['student:id,nama,nis', 'feeType:id,nama'])
                ->when($request->string('status')->isNotEmpty(), fn ($q) => $q->where('status', $request->string('status')))
                ->orderByDesc('created_at')
                ->paginate(20)
        );
    }

    public function invoiceGenerate(Request $request, KeuanganService $service): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'fee_type_id' => ['required', 'integer', 'exists:fee_types,id'],
            'periode_label' => ['required', 'string', 'max:30'],
            'jatuh_tempo' => ['nullable', 'date'],
        ]);

        $count = $service->generateInvoices(
            (int) $v['fee_type_id'],
            (string) $v['periode_label'],
            $v['jatuh_tempo'] ?? null
        );

        return response()->json(['message' => 'Tagihan dibuat.', 'count' => $count], 201);
    }

    public function invoiceShow(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load(['student:id,nama,nis', 'feeType:id,nama', 'payments']));
    }

    // ---------- Pembayaran ----------

    public function paymentStore(Request $request, Invoice $invoice, KeuanganService $service): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'nominal' => ['required', 'numeric', 'min:1'],
            'tgl_bayar' => ['required', 'date'],
            'metode' => ['required', Rule::in(array_keys(Payment::METODES))],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $payment = $service->recordPayment($invoice, [
                'nominal' => (float) $v['nominal'],
                'tgl_bayar' => (string) $v['tgl_bayar'],
                'metode' => (string) $v['metode'],
                'catatan' => $v['catatan'] ?? null,
            ], $request->user()->id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($payment, 201);
    }

    // ---------- Laporan ----------

    public function summary(KeuanganService $service): JsonResponse
    {
        return response()->json($service->summary());
    }

    public function arrears(Request $request): JsonResponse
    {
        return response()->json(
            Invoice::with(['student:id,nama,nis', 'feeType:id,nama'])
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->when($request->integer('student_id'), fn ($q, $id) => $q->where('student_id', $id))
                ->orderBy('jatuh_tempo')
                ->paginate(20)
        );
    }
}

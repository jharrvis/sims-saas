<?php

declare(strict_types=1);

namespace App\Domains\Keuangan\Services;

use App\Domains\Pwa\Services\PushService;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Logika tagihan & pembayaran SPP.
 */
class KeuanganService
{
    public function __construct(
        private readonly TenantContext $tenant,
        private readonly PushService $push,
    ) {}

    /**
     * Generate tagihan massal untuk semua siswa aktif.
     *
     * @return int jumlah tagihan dibuat
     */
    public function generateInvoices(int $feeTypeId, string $periodeLabel, ?string $jatuhTempo = null): int
    {
        $feeType = FeeType::findOrFail($feeTypeId);
        $count = 0;

        Student::where('status', 'aktif')->chunk(200, function ($students) use ($feeType, $periodeLabel, $jatuhTempo, &$count): void {
            foreach ($students as $student) {
                $exists = Invoice::where('student_id', $student->id)
                    ->where('fee_type_id', $feeType->id)
                    ->where('periode_label', $periodeLabel)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $invoice = Invoice::create([
                    'student_id' => $student->id,
                    'fee_type_id' => $feeType->id,
                    'no_invoice' => $this->generateNoInvoice(),
                    'periode_label' => $periodeLabel,
                    'nominal' => $feeType->nominal,
                    'jatuh_tempo' => $jatuhTempo,
                    'status' => 'unpaid',
                ]);
                $count++;

                $this->push->notifyNewInvoice($invoice->load(['student', 'feeType']));
            }
        });

        return $count;
    }

    /**
     * Catat pembayaran; update dibayar + status tagihan (transaksional).
     *
     * @param  array{nominal:float, tgl_bayar:string, metode:string, catatan?:?string}  $data
     *
     * @throws \InvalidArgumentException jika nominal melebihi sisa
     */
    public function recordPayment(Invoice $invoice, array $data, int $inputBy): Payment
    {
        $sisa = $invoice->sisa();

        if ($data['nominal'] <= 0 || $data['nominal'] > $sisa) {
            throw new \InvalidArgumentException(
                'Nominal pembayaran harus antara 1 dan '.number_format($sisa, 0, ',', '.').'.'
            );
        }

        return DB::transaction(function () use ($invoice, $data, $inputBy): Payment {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'no_kuitansi' => $this->generateNoKuitansi(),
                'nominal' => $data['nominal'],
                'tgl_bayar' => $data['tgl_bayar'],
                'metode' => $data['metode'],
                'catatan' => $data['catatan'] ?? null,
                'input_by' => $inputBy,
            ]);

            $dibayarBaru = (float) $invoice->dibayar + (float) $data['nominal'];
            $status = $dibayarBaru >= (float) $invoice->nominal ? 'paid' : 'partial';

            $invoice->update(['dibayar' => $dibayarBaru, 'status' => $status]);

            return $payment;
        });
    }

    /**
     * Tandai tagihan yang lewat jatuh tempo sebagai overdue.
     *
     * @return int jumlah tagihan ditandai
     */
    public function markOverdue(): int
    {
        return Invoice::whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('jatuh_tempo')
            ->whereDate('jatuh_tempo', '<', today())
            ->update(['status' => 'overdue']);
    }

    /**
     * Ringkasan arus kas: total tagihan, terkumpul, tunggakan.
     *
     * @return array{total_tagihan:float, terkumpul:float, tunggakan:float, jumlah_unpaid:int}
     */
    public function summary(): array
    {
        $q = Invoice::query();

        return [
            'total_tagihan' => (float) $q->sum('nominal'),
            'terkumpul' => (float) $q->sum('dibayar'),
            'tunggakan' => (float) $q->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum(DB::raw('nominal - dibayar')),
            'jumlah_unpaid' => (int) $q->whereIn('status', ['unpaid', 'partial', 'overdue'])->count(),
        ];
    }

    private function generateNoInvoice(): string
    {
        $schoolId = $this->tenant->requireSchoolId();

        $count = Invoice::where('school_id', $schoolId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('INV/%s/%05d', now()->format('Y'), $count);
    }

    private function generateNoKuitansi(): string
    {
        $schoolId = $this->tenant->requireSchoolId();

        $count = Payment::where('school_id', $schoolId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('KWT/%s/%05d', now()->format('Y'), $count);
    }
}

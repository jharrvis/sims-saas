<?php

declare(strict_types=1);

namespace App\Domains\Billing\Services;

use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Platform\Services\PlatformService;
use App\Models\BillingInvoice;
use App\Models\DunningLog;
use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use App\Scopes\SchoolScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Siklus hidup langganan otomatis (dijadwalkan harian):
 * pengingat trial, ekspirasi trial → past_due → suspend,
 * invoice overdue, dan perpanjangan otomatis.
 */
class DunningService
{
    /**
     * Masa tenggang past_due sebelum suspend otomatis (hari).
     */
    public const GRACE_HARI = 7;

    public function __construct(
        private readonly PlatformService $platform,
        private readonly WaSender $wa,
    ) {}

    /**
     * @return array<string, int>
     */
    public function proses(): array
    {
        $hasil = [
            'trial_reminder' => 0,
            'trial_expired' => 0,
            'suspended' => 0,
            'invoice_overdue' => 0,
            'renewal_issued' => 0,
        ];

        DB::transaction(function () use (&$hasil): void {
            $hasil['trial_reminder'] = $this->prosesTrialReminder();
            $hasil['trial_expired'] = $this->prosesTrialExpired();
            $hasil['suspended'] = $this->prosesSuspendOtomatis();
            $hasil['invoice_overdue'] = $this->prosesInvoiceOverdue();
            $hasil['renewal_issued'] = $this->prosesPerpanjangan();
        });

        return $hasil;
    }

    /**
     * Pengingat H-7 / H-3 / H-1 sebelum trial berakhir.
     */
    private function prosesTrialReminder(): int
    {
        $count = 0;

        foreach ([7 => 'trial_h7', 3 => 'trial_h3', 1 => 'trial_h1'] as $hari => $jenis) {
            $subs = Subscription::withoutGlobalScope(SchoolScope::class)
                ->where('status', 'trial')
                ->whereDate('trial_berakhir_pada', now()->addDays($hari)->toDateString())
                ->get();

            foreach ($subs as $sub) {
                $school = $this->school($sub->school_id);

                if ($school === null || $this->sudahKirim($school->id, $jenis)) {
                    continue;
                }

                /** @var Carbon $berakhir */
                $berakhir = $sub->trial_berakhir_pada;

                $this->kirimWa($school,
                    "Halo {$school->nama}, masa trial SIMS Anda berakhir dalam {$hari} hari ".
                    "({$berakhir->format('d M Y')}). ".
                    'Berlangganan agar layanan tidak terhenti: '.url('/langganan'));

                $this->catat($school->id, $jenis);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Trial yang sudah lewat → past_due (masa tenggang).
     */
    private function prosesTrialExpired(): int
    {
        $count = 0;

        $subs = Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('status', 'trial')
            ->where('trial_berakhir_pada', '<', now()->startOfDay())
            ->get();

        foreach ($subs as $sub) {
            $school = $this->school($sub->school_id);

            if ($school === null) {
                continue;
            }

            $sub->update(['status' => 'past_due']);
            $school->update(['status_langganan' => 'past_due']);

            if (! $this->sudahKirim($school->id, 'trial_expired')) {
                $this->kirimWa($school,
                    "Halo {$school->nama}, masa trial SIMS Anda telah berakhir. ".
                    'Segera berlangganan dalam '.self::GRACE_HARI.' hari agar akses tidak ditangguhkan: '.url('/langganan'));

                $this->catat($school->id, 'trial_expired');
            }

            $count++;
        }

        return $count;
    }

    /**
     * past_due melewati masa tenggang → suspend otomatis.
     */
    private function prosesSuspendOtomatis(): int
    {
        $count = 0;

        $subs = Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('status', 'past_due')
            ->where('updated_at', '<', now()->subDays(self::GRACE_HARI))
            ->get();

        foreach ($subs as $sub) {
            $school = $this->school($sub->school_id);

            if ($school === null) {
                continue;
            }

            $this->platform->suspend($school, null, 'Suspend otomatis: melewati masa tenggang '.self::GRACE_HARI.' hari');

            if (! $this->sudahKirim($school->id, 'suspended')) {
                $this->kirimWa($school,
                    "Halo {$school->nama}, akses SIMS Anda ditangguhkan karena tagihan belum dibayar. ".
                    'Lunasi tagihan untuk mengaktifkan kembali: '.url('/langganan'));

                $this->catat($school->id, 'suspended');
            }

            $count++;
        }

        return $count;
    }

    /**
     * Tagihan issued lewat jatuh tempo → overdue + pengingat.
     */
    private function prosesInvoiceOverdue(): int
    {
        $count = 0;

        $invoices = BillingInvoice::withoutGlobalScope(SchoolScope::class)
            ->where('status', 'issued')
            ->where('jatuh_tempo', '<', now()->toDateString())
            ->get();

        foreach ($invoices as $invoice) {
            $school = $invoice->school_id ? $this->school($invoice->school_id) : null;

            $invoice->update(['status' => 'overdue']);

            if ($school === null || $this->sudahKirim($school->id, 'invoice_overdue', $invoice->nomor)) {
                continue;
            }

            $this->kirimWa($school,
                "Halo {$school->nama}, tagihan {$invoice->nomor} sebesar ".
                'Rp'.number_format($invoice->total, 0, ',', '.').' telah jatuh tempo. '.
                'Bayar sekarang: '.url('/langganan'));

            $this->catat($school->id, 'invoice_overdue', $invoice->nomor);
            $count++;
        }

        return $count;
    }

    /**
     * Terbitkan tagihan perpanjangan H-7 sebelum periode berakhir.
     */
    private function prosesPerpanjangan(): int
    {
        $count = 0;

        $subs = Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('status', 'active')
            ->whereDate('periode_berakhir_pada', '<=', now()->addDays(7)->toDateString())
            ->with('plan')
            ->get();

        foreach ($subs as $sub) {
            // Jangan duplikat: sudah ada tagihan issued/overdue untuk periode ini.
            $ada = BillingInvoice::withoutGlobalScope(SchoolScope::class)
                ->where('subscription_id', $sub->id)
                ->whereIn('status', ['issued', 'overdue'])
                ->exists();

            if ($ada) {
                continue;
            }

            $invoice = $this->platform->issueInvoice($sub, null);

            $school = $this->school($sub->school_id);

            if ($school !== null) {
                $this->kirimWa($school,
                    "Halo {$school->nama}, tagihan perpanjangan {$invoice->nomor} sebesar ".
                    'Rp'.number_format($invoice->total, 0, ',', '.').' telah terbit. '.
                    'Bayar sebelum jatuh tempo: '.url('/langganan'));

                $this->catat($school->id, 'renewal_issued', $invoice->nomor);
            }

            $count++;
        }

        return $count;
    }

    private function school(int $schoolId): ?School
    {
        return School::withoutGlobalScope(SchoolScope::class)->find($schoolId);
    }

    private function sudahKirim(int $schoolId, string $jenis, ?string $referensi = null): bool
    {
        return DunningLog::where('school_id', $schoolId)
            ->where('jenis', $jenis)
            ->where('referensi', $referensi)
            ->exists();
    }

    private function catat(int $schoolId, string $jenis, ?string $referensi = null): void
    {
        DunningLog::create([
            'school_id' => $schoolId,
            'jenis' => $jenis,
            'referensi' => $referensi,
            'channel' => 'wa',
            'dikirim_pada' => now(),
        ]);
    }

    /**
     * Kirim ke penanggung jawab sekolah (membership pertama = admin pendaftar).
     */
    private function kirimWa(School $school, string $pesan): void
    {
        $membership = Membership::where('school_id', $school->id)
            ->where('status', 'aktif')
            ->orderBy('id')
            ->first();

        $phone = $membership ? User::whereKey($membership->user_id)->value('phone') : null;

        if (! $phone) {
            Log::warning('dunning: tanpa nomor WA penanggung jawab', ['school_id' => $school->id]);

            return;
        }

        try {
            $this->wa->sendText($phone, $pesan);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Platform\Services;

use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use App\Models\User;
use App\Scopes\SchoolScope;
use Illuminate\Support\Collection;

/**
 * Operasional platform SaaS: metrik, siklus hidup tenant, langganan.
 * Semua akses lintas tenant memakai withoutGlobalScope eksplisit
 * dan dicatat di audit (Dok 02 §10).
 */
class PlatformService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        $tenants = School::withoutGlobalScope(SchoolScope::class);

        $perStatus = (clone $tenants)
            ->selectRaw('status_langganan, count(*) as jumlah')
            ->groupBy('status_langganan')
            ->pluck('jumlah', 'status_langganan');

        $mrr = Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('status', 'active')
            ->with('plan')
            ->get()
            ->sum(fn (Subscription $s) => $s->periode === 'tahunan'
                ? intdiv($s->plan->harga_tahunan ?? $s->plan->harga_bulanan * 12, 12)
                : $s->plan->harga_bulanan);

        return [
            'total_tenant' => (clone $tenants)->count(),
            'per_status' => $perStatus,
            'mrr' => $mrr,
            'trial_berakhir_7_hari' => Subscription::withoutGlobalScope(SchoolScope::class)
                ->where('status', 'trial')
                ->whereBetween('trial_berakhir_pada', [now(), now()->addDays(7)])
                ->count(),
            'invoice_overdue' => BillingInvoice::withoutGlobalScope(SchoolScope::class)
                ->where('status', 'overdue')
                ->count(),
            'piutang' => BillingInvoice::withoutGlobalScope(SchoolScope::class)
                ->whereIn('status', ['issued', 'overdue'])
                ->sum('total'),
        ];
    }

    /**
     * @return Collection<int, School>
     */
    public function tenants(?string $status = null, ?string $search = null): Collection
    {
        $schools = School::withoutGlobalScope(SchoolScope::class)
            ->when($status, fn ($q) => $q->where('status_langganan', $status))
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', "%{$search}%")
                ->orWhere('npsn', 'like', "%{$search}%")))
            ->orderBy('nama')
            ->get();

        // Eager load manual: withoutGlobalScope tidak diwariskan ke relasi.
        $subs = Subscription::withoutGlobalScope(SchoolScope::class)
            ->whereIn('school_id', $schools->pluck('id'))
            ->with('plan')
            ->orderByDesc('id')
            ->get()
            ->groupBy('school_id');

        return $schools->each(
            fn (School $s) => $s->setRelation('subscription', $subs->get($s->id)?->first())
        );
    }

    public function findTenant(int $schoolId): School
    {
        return School::withoutGlobalScope(SchoolScope::class)->findOrFail($schoolId);
    }

    /**
     * Suspend tenant: kunci tulis (402 di ResolveTenant) + catat audit.
     */
    public function suspend(School $school, ?User $by, string $alasan = ''): void
    {
        $school->update(['status_langganan' => 'suspended']);
        $this->syncSubscription($school, 'suspended');

        $this->audit($by, 'platform.tenant.suspend', $school, ['alasan' => $alasan]);
    }

    public function activate(School $school, User $by): void
    {
        $school->update(['status_langganan' => 'active']);
        $this->syncSubscription($school, 'active');

        $this->audit($by, 'platform.tenant.activate', $school, []);
    }

    /**
     * Buat langganan baru (termasuk trial 30 hari) untuk tenant.
     */
    public function createSubscription(School $school, Plan $plan, string $periode, User $by, bool $trial = false): Subscription
    {
        Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $school->id)
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->update(['status' => 'cancelled', 'dibatalkan_pada' => now()]);

        $sub = Subscription::withoutGlobalScope(SchoolScope::class)->create([
            'school_id' => $school->id,
            'plan_id' => $plan->id,
            'status' => $trial ? 'trial' : 'active',
            'periode' => $periode,
            'trial_berakhir_pada' => $trial ? now()->addDays(30) : null,
            'periode_berakhir_pada' => $trial ? null : now()->add($periode === 'tahunan' ? '1 year' : '1 month'),
        ]);

        $school->update(['status_langganan' => $trial ? 'trialing' : 'active']);

        $this->audit($by, 'platform.subscription.create', $school, [
            'plan' => $plan->slug, 'periode' => $periode, 'trial' => $trial,
        ]);

        return $sub;
    }

    /**
     * Terbitkan tagihan langganan (Midtrans menyusul di langkah onboarding).
     */
    public function issueInvoice(Subscription $sub, ?User $by = null, int $diskon = 0): BillingInvoice
    {
        $school = $this->findTenant($sub->school_id);
        $plan = $sub->plan;
        $harga = $plan->hargaPeriode($sub->periode);
        $total = max(0, $harga - $diskon);

        $dari = now()->startOfDay();
        $sampai = (clone $dari)->add($sub->periode === 'tahunan' ? '1 year' : '1 month')->subDay();

        $invoice = BillingInvoice::withoutGlobalScope(SchoolScope::class)->create([
            'school_id' => $school->id,
            'nama_sekolah_snapshot' => $school->nama,
            'npsn_snapshot' => $school->npsn,
            'subscription_id' => $sub->id,
            'nomor' => $this->nextNomor(),
            'periode_dari' => $dari->toDateString(),
            'periode_sampai' => $sampai->toDateString(),
            'subtotal' => $harga,
            'diskon' => $diskon,
            'pajak' => 0,
            'total' => $total,
            'status' => 'issued',
            'jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $this->audit($by, 'platform.invoice.issue', $school, [
            'nomor' => $invoice->nomor, 'total' => $total,
        ]);

        return $invoice;
    }

    public function markInvoicePaid(BillingInvoice $invoice, User $by, string $metode = 'manual'): void
    {
        $invoice->update([
            'status' => 'paid',
            'dibayar_pada' => now(),
            'metode_bayar' => $metode,
        ]);

        $school = $this->findTenant((int) $invoice->school_id);

        if ($school->status_langganan === 'past_due') {
            $school->update(['status_langganan' => 'active']);
            $this->syncSubscription($school, 'active');
        }

        $this->audit($by, 'platform.invoice.paid', $school, [
            'nomor' => $invoice->nomor, 'metode' => $metode,
        ]);
    }

    private function syncSubscription(School $school, string $status): void
    {
        Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $school->id)
            ->whereIn('status', ['trial', 'active', 'past_due', 'suspended'])
            ->update(['status' => $status]);
    }

    private function nextNomor(): string
    {
        $prefix = 'INV/'.now()->format('Y/m').'/';

        $last = BillingInvoice::withoutGlobalScope(SchoolScope::class)
            ->where('nomor', 'like', $prefix.'%')
            ->orderByDesc('nomor')
            ->value('nomor');

        $seq = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        return $prefix.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $after
     */
    private function audit(?User $by, string $aksi, School $school, array $after): void
    {
        AuditLog::create([
            'school_id' => $school->id,
            'user_id' => $by?->id,
            'aksi' => $aksi,
            'model_type' => School::class,
            'model_id' => (string) $school->id,
            'after' => $after,
            'ip_address' => request()->ip(),
        ]);
    }
}

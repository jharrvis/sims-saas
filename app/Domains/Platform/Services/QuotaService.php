<?php

declare(strict_types=1);

namespace App\Domains\Platform\Services;

use App\Models\Plan;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\TenantQuotaOverride;
use App\Scopes\SchoolScope;
use App\Tenancy\TenantContext;

/**
 * Evaluasi batas kuota tenant (Dok 02 §8.13).
 * Urutan: (1) override yang masih berlaku → (2) batas paket plans
 * → (3) batas trial bila status langganan trial.
 * Override TIDAK berlaku untuk masa trial (PRD §9.3).
 */
class QuotaService
{
    /**
     * Batas formal masa trial (PRD §9.3/§9.5).
     *
     * @var array<string, int|null>
     */
    public const TRIAL_LIMITS = [
        'siswa_aktif' => 100,
        'user_staf' => 10,
        'storage_mb' => 1024,
    ];

    /**
     * @return array{batas: int|null, terpakai: int, sisa: int|null, boleh: bool}
     */
    public function check(string $metrik): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();
        $batas = $this->batas($metrik, $schoolId);
        $terpakai = $this->terpakai($metrik, $schoolId);

        return [
            'batas' => $batas,
            'terpakai' => $terpakai,
            'sisa' => $batas === null ? null : max(0, $batas - $terpakai),
            'boleh' => $batas === null || $terpakai < $batas,
        ];
    }

    /**
     * Fitur boolean: broadcast & API hanya untuk pelanggan berbayar.
     */
    public function allows(string $fitur): bool
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $status = School::withoutGlobalScope(SchoolScope::class)
            ->whereKey($schoolId)->value('status_langganan');

        // Trial: tanpa broadcast massal & tanpa API (PRD §9.5).
        if ($status === 'trialing') {
            return ! in_array($fitur, ['broadcast', 'api'], true);
        }

        return true;
    }

    private function batas(string $metrik, int $schoolId): ?int
    {
        $isTrial = $this->isTrial($schoolId);

        // (1) Override — tidak berlaku saat trial.
        if (! $isTrial) {
            $override = TenantQuotaOverride::withoutGlobalScope(SchoolScope::class)
                ->where('school_id', $schoolId)
                ->where('metrik', $metrik)
                ->where(fn ($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now()))
                ->first();

            if ($override) {
                return $override->batas_custom;
            }

            // (2) Batas paket.
            $plan = $this->activePlan($schoolId);

            if ($plan) {
                $kolom = match ($metrik) {
                    'siswa_aktif' => 'max_siswa',
                    'user_staf' => 'max_user_staf',
                    'storage_mb' => 'max_storage_mb',
                    default => null,
                };

                if ($kolom !== null) {
                    return $plan->{$kolom};
                }
            }
        }

        // (3) Batas trial.
        return self::TRIAL_LIMITS[$metrik] ?? null;
    }

    private function terpakai(string $metrik, int $schoolId): int
    {
        return match ($metrik) {
            'siswa_aktif' => Student::withoutGlobalScope(SchoolScope::class)
                ->where('school_id', $schoolId)->where('status', 'aktif')->count(),
            default => 0,
        };
    }

    private function isTrial(int $schoolId): bool
    {
        $status = School::withoutGlobalScope(SchoolScope::class)
            ->whereKey($schoolId)->value('status_langganan');

        return $status === 'trialing';
    }

    private function activePlan(int $schoolId): ?Plan
    {
        $planId = Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', $schoolId)
            ->whereIn('status', ['active', 'past_due'])
            ->orderByDesc('id')
            ->value('plan_id');

        return $planId ? Plan::find($planId) : null;
    }
}
